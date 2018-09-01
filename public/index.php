<?php session_start();

define('TIME_START', microtime(true));

require_once __DIR__.'/../app/config/config.php';
require_once __DIR__.'/../vendor/autoload.php';



if(GetIP() != '127.0.0.1'){
    //die('Site Under Construction');
}


Config::init();
User::init();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Cloud\Cache;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => DEBUG, 'determineRouteBeforeAppMiddleware' => true]]);

//Login middleware
$auth_mw = function ($request, $response, $next) {

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

$redir_mw = function ($request, $response, $next) {
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

$episode_mw = function ($request, $response, $next) {
    $slug = trim($request->getAttribute('routeInfo')[2]['slug'],'/');
    if(Cache::exists("404:$slug")){
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
    return $response = $next($request, $response);
};

//Middleware
$app->add(function (Request $request, Response $response, callable $next) {

    //Load anime list into memory
    if(!Cache::exists('animes')){
        Anime::query();
        Cache::save('animes','loaded');
    }

    if($request->isGet()){
        if($route = $request->getAttribute('route')){
            $page = $route->getName();
            define('PAGE', strtolower($page));
        }
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
$app->get('/', 'IndexController:home')->setName('home');
$app->post('/paginate', 'AjaxController:paginate');
$app->post('/genres', 'AjaxController:genre_ajax');
$app->get('/settings', 'IndexController:settings')->setName('settings')->add($auth_mw);
$app->post('/settings', 'IndexController:settings')->add($auth_mw);
$app->post('/settings/reload', 'IndexController:reloadSettings')->add($auth_mw);
$app->post('/settings/sitemap', 'IndexController:generateSitemap')->add($auth_mw);
$app->post('/api/cache/purge', 'IndexController:purge');

//Authentication routes
$app->get('/login', 'AuthController:login')->setName('login');
$app->post('/login', 'AuthController:loginHandler');
$app->get('/login/connect', 'AuthController:connect')->setName('connect');
$app->get('/logout', 'AuthController:logout')->setName('logout');

//Anime route
$app->get(Config::get('anime')['base_url'].'/{slug}[/]', 'AnimeController:get')->setName('anime');
//Episode route
$app->get(Config::get('episode')['base_url'].'/{slug}[/]', 'EpisodeController:get')->setName('episode');

//Cartoon route
$app->get(Config::get('cartoon')['base_url'].'/{slug}[/]', 'CartoonController:get')->setName('cartoon');
//Cartoon Episode route
$app->get(Config::get('cartoon_episode')['base_url'].'/{slug}[/]', 'CartoonController:getEpisode')->setName('cartoon_episode');

foreach (Config::get('pages') as $page){
    $app->get($page['base_url'].'[/{params:.*}]', function ($request, $response, $args) use ($page){
        $params = (isset($args['params'])) ? explode('/', rtrim($args['params'],'/')) : null;
        $this->view->render($response, TEMPLATE_BASE.$page['template_file'], [
            'title' => $page['title'],
            'description' => $page['desc'],
            'params' => $params,
        ]);
        return $response;
    })->setName($page['title']);
}

$app->get('/{slug}', 'EpisodeController:get')->setName('episode_old')->add($episode_mw);

try{
    $app->run();
} catch (Exception $e){
    echo $e->getMessage();
}