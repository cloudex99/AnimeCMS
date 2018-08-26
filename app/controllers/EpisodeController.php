<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:27 PM
 */

use \Cloud\Cache;

class EpisodeController extends Controller
{
    public function get($request, $response, $args)
    {
        $slug = $args['slug'];
        $id = Functions::get_id_from_slug($slug);

        if (Cache::exists("404:$slug")) {
            $response = $this->error404($request, $response, $args);
        }

        if ($episode = Episode::get(['id' => $id])) {

            if(DUBBED_ONLY){
                $episode->setEpisodeType('dubbed');
            }
            $episode->setDefault('trollvid');

            if (Functions::matchSlug('subbed')) {
                $episode->subbedFirst();
            } else {
                $episode->dubbedFirst();
            }

            $this->view->render($response, TEMPLATE_BASE . 'episode.php', [
                'episode' => $episode,
                'title' => $episode->page_title(),
                'description' => $episode->meta_description()
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