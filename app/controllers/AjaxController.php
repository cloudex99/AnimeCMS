<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:26 PM
 */

class AjaxController extends Controller
{

    public function genre_ajax($request, $response, $args)
    {
        $animes = [];
        if (isset($_POST['genres'])) {
            $genres = $_POST['genres'];
            $genres = implode(',', $genres);
            $animes = Anime::query("genre=$genres");
        }
        return $response->withJson($animes);
    }

    public function paginate($request, $response, $args){

        $type = $_POST['type'] ?? null;
        $size = $_POST['size'] ?? 10;
        $page = $_POST['page'] ?? 1;
        $order = $_POST['order'] ?? 'desc';

        switch ($type) {
            case 'anime_list':
                $paginate = paginate(Anime::query(), $size, $page);
                break;
            case 'anime_query':
                $paginate = paginate(Anime::query($_POST['query']), $size, $page);
                break;
            case 'anime_episodes':
                $paginate = ($order === 'asc') ? paginate(array_reverse(Anime::get(['id' => $_POST['anime_id']])->getEpisodes()), $size, $page) : paginate(Anime::get(['id' => $_POST['anime_id']])->getEpisodes(), $size, $page);
                break;
            case 'anime_subbed':
                $paginate = paginate(Anime::latest('latest', 'subbed',100), $size, $page);
                break;
            case 'anime_dubbed':
                $paginate = paginate(Anime::latest('latest', 'dubbed',100), $size, $page);
                break;
            case 'episodes_subbed':
                $paginate = paginate(Episode::latest('subbed', 100), $size, $page);
                break;
            case 'episodes_dubbed':
                $paginate = paginate(Episode::latest('dubbed', 100), $size, $page);
                break;
            default:
                $paginate = [];
        }

        return $response->withJson($paginate);
    }

}