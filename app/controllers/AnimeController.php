<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:27 PM
 */

class AnimeController extends Controller
{

    public function get($request, $response, $args)
    {

        $slug = $args['slug'];
        $id = Functions::get_id_from_slug($slug);

        if (is_numeric($id) && $anime = Anime::get(['id' => $id])) {
            $this->view->render($response, TEMPLATE_BASE . 'anime.php', [
                'anime' => $anime,
                'title' => $anime->page_title(),
                'description' => $anime->meta_description(),
                'scripts' => Config::get('anime')['scripts']
            ]);
        } elseif ($anime = Anime::get(['slug' => $slug])) {
            $response = $response->withRedirect($anime->url())->withStatus(301);
        } else {
            $response = $this->error404($request, $response, $args);
        }

        return $response;

    }

}
