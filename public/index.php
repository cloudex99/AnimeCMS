<?php session_start();

define('TIME_START', microtime(true));

require_once '../app/config/config.php';
require_once('../vendor/autoload.php');

Config::init();
User::init();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => DEBUG, 'determineRouteBeforeAppMiddleware' => true]]);

//Login middleware
$mw = function ($request, $response, $next) {

    User::init();

    if (!User::isLoggedIn())
    {
        // redirect the user to the login page and do not proceed.
        $response = $response->withRedirect(SITE_URL.'/login');
    }
    elseif(!User::hasAccess()){
        $_SESSION['_msg'] = 'Not allowed';
        $response = $response->withRedirect('/');
    }
    else {
        // Proceed as normal...
        $response = $next($request, $response);
    }

    return $response;
};

$redir = function ($request, $response, $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if($_SERVER['HTTP_HOST'] !== parse_url(SITE_URL, PHP_URL_HOST)){
        return $response = $response->withRedirect(SITE_URL)->withStatus(301);
    } else {
        //Proceed as normal...
        $response = $next($request, $response);
    }
    return $response;
};

$ep_mw = function ($request, $response, $next) {
    $slug = trim($request->getAttribute('routeInfo')[2]['slug'],'/');
    if(Cloud\Cache::exists("404:$slug")){
        if($_SERVER['HTTP_HOST'] !== parse_url(SITE_URL, PHP_URL_HOST)){
            return $response = $response->withRedirect(SITE_URL)->withStatus(301);
        }
        $this->view->render($response, TEMPLATE_BASE.'404.php', [
            'description' => '404 Not Found',
        ]);
        return $response = $response->withStatus(404);
    }
    if($episode = Episode::get(['slug' => $slug])){
        return $response = $response->withRedirect($episode->url())->withStatus(301);
    }
    if($id = Cloud\Cache::fetch("e:$slug")){
        return $response = $response->withRedirect(Episode::get(['id' => $id])->url())->withStatus(301);
    }

    if($_SERVER['HTTP_HOST'] !== parse_url(SITE_URL, PHP_URL_HOST)){
        return $response = $response->withRedirect(SITE_URL)->withStatus(301);
    }
    return $response = $next($request, $response);
};

$anime_mw = function ($request, $response, $next) {
    $slug = $request->getAttribute('routeInfo')[2]['slug'];
    if($anime = Anime::get(['slug' => $slug])){
        return $response = $response->withRedirect($anime->url())->withStatus(301);
    }
    if($id = Cloud\Cache::fetch("a:$slug")){
        return $response = $response->withRedirect(Anime::get(['id' => $id])->url())->withStatus(301);
    }
    return $response = $next($request, $response);
};

//Middleware
$app->add(function (Request $request, Response $response, callable $next) {

    //Load anime list into memory
    if(!Cloud\Cache::exists('animes')){
        Anime::query();
        Cloud\Cache::save('animes','loaded');
    }

    /**
     * Delete this block if you are starting a new site.
     */
    //Load legacy slugs onto memory
    if(!Cloud\Cache::exists('slugs')){
        $slugs = json_decode(file_get_contents(BASE_DIR.'/data/slugs.json'));
        foreach ($slugs->episodes as $ep){
            if(!Cloud\Cache::exists('e:'.$ep->slug)){
                Cloud\Cache::save('e:'.$ep->slug,$ep->id);
            }
        }
        foreach ($slugs->anime as $anime){
            if(!Cloud\Cache::exists('a:'.$anime->slug)){
                Cloud\Cache::save('a:'.$anime->slug,$anime->id);
            }
        }
        Cloud\Cache::save('slugs','loaded');
    }

    //Remove trailing slashes middleware
    $path = $_SERVER['REQUEST_URI'];
    if ($path != '/' && substr($path, -1) == '/') {
        if($request->getMethod() == 'GET') {
            return $response->withRedirect(SITE_URL.rtrim($path,'/'), 301);
        }
    }


    return $next($request, $response);
});

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($c) {
    return new \Slim\Views\PhpRenderer(BASE_DIR.'app/views/');
};

//Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response->withStatus(404), TEMPLATE_BASE.'404.php',[
            'title' => '404 Not Found'
        ]);
    };
};

//Setup routes
$app->get('/', 'IndexController:home');
$app->post('/paginate', 'AjaxController:paginate');
$app->post('/genres', 'AjaxController:genre_ajax');
$app->get('/settings', 'IndexController:settings')->add($mw);
$app->post('/settings', 'IndexController:settings')->add($mw);
$app->post('/settings/reload', 'IndexController:reloadSettings')->add($mw);
$app->post('/settings/sitemap', 'IndexController:generateSitemap')->add($mw);
$app->post('/api/cache/purge', 'IndexController:purge');

//Authentication routes
$app->get('/login', 'AuthController:login')->setName('login');
$app->post('/login', 'AuthController:loginHandler')->setName('login');
$app->get('/login/connect', 'AuthController:connect')->setName('connect');
$app->get('/logout', 'AuthController:logout')->setName('logout');

//Anime route
$app->get(Config::get('anime')['base_url'].'/{slug}[/]', 'AnimeController:get');

//Episode route
$app->get(Config::get('episode')['base_url'].'/{slug}[/]', 'EpisodeController:get');

/**
 * Edit the legacy routes according to your previous url structure.
 * This will redirect your old links to the new ones with 301.
 * If this is a new site then delete the legacy routes.
 */
//Legacy anime route
$app->get('/series-info/{slug}[/]', 'AnimeController:get')->add($anime_mw);
$app->get('/m/series-info/{slug}[/]', 'AnimeController:get')->add($anime_mw);

//Legacy episode route
$app->get('/anime/{p:watch|dubbed|subbed}/{subdub}/{slug}[/]', 'EpisodeController:get')->add($ep_mw);
$app->get('/m/watch/{p:watch|anime|dubbed|subbed}/{subdub}/{slug}[/]', 'EpisodeController:get')->add($ep_mw);
$app->get('/watch/anime/{subdub}/{slug}[/]', 'EpisodeController:get')->add($ep_mw);

foreach (Config::get('pages') as $page){
    $app->get($page['base_url'].'[/{params:.*}]', function ($request, $response, $args) use ($page){
        $params = (isset($args['params'])) ? explode('/', rtrim($args['params'],'/')) : null;
        $this->view->render($response, TEMPLATE_BASE.$page['template_file'], [
            'title' => $page['title'],
            'description' => $page['desc'],
            'params' => $params,
            'scripts' => $page['scripts']
        ]);
        return $response;
    });
}

$app->get('/{slug}', 'EpisodeController:get')->add($ep_mw);

try{
    $app->run();
} catch (Exception $e){
    echo $e->getMessage();
}