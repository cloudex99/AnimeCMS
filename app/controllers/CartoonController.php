<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-22
 * Time: 7:32 PM
 */

use Cloud\Cache;

class CartoonController extends Controller
{

    public function get($request, $response, $args)
    {
        $slug = $args['slug'];
        $id = Functions::get_id_from_slug($slug);

        if (is_numeric($id) && $cartoon = Cartoon::get($id)) {
            $this->view->render($response, TEMPLATE_BASE . 'cartoon.php', [
                'cartoon' => $cartoon,
                'title' => $cartoon->page_title(),
                'description' => $cartoon->meta_description(),
                'scripts' => Config::get('cartoon')['scripts']
            ]);
        }else {
            $response = $this->error404($request, $response, $args);
        }

        return $response;
    }
    
    public function getEpisode($request, $response, $args){

        $slug = $args['slug'];
        $id = Functions::get_id_from_slug($slug);

        if (Cache::exists("404:$slug")) {
            $response = $this->error404($request, $response, $args);
        }

        if ($episode = CartoonEpisode::get($id)) {
            $this->view->render($response, TEMPLATE_BASE . 'cartoon-episode.php', [
                'episode' => $episode,
                'title' => $episode->page_title(),
                'description' => $episode->meta_description(),
                'scripts' => Config::get('cartoon_episode')['scripts']
            ]);
        } else {
            if (!Cache::exists("404:$slug")) {
                Cache::save("404:$slug", true, 3600);
            }
            $response = $this->error404($request, $response, $args);
        }

        return $response;
        
    }
}