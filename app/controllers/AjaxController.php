<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:26 PM
 */
use \Cloud\Cache;
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
                $query = Anime::query();
                $data = ['total' => count($query), 'data' => paginate($query, $size, $page)];
                break;
            case 'anime_query':
                $query = '';
                if(is_array($_POST['query'])){
                    foreach ($_POST['query'] as $i => $q){
                        if($q=='0' || $q == ''){
                            continue;
                        }
                        $query.="$i=$q&";
                    }
                } else {
                    $query = $_POST['query'];
                }

                $query=rtrim($query,'&');
                $results = Anime::query($query);
                if(isset($_POST['letter']) && $_POST['letter']!=='all'){
                    $results = Anime::letters($results, $_POST['letter']);
                }
                $pages = ceil(count($results)/$size);
                $data = ['results' => paginate($results, $size, $page), 'pages' => $pages, 'query' => $query];
                break;
            case 'anime_episodes':
                $data = ($order === 'asc') ? paginate(array_reverse(Cache::fetch($_POST['anime_id'])->getEpisodes()), $size, $page) : paginate(Cache::fetch($_POST['anime_id'])->getEpisodes(), $size, $page);
                break;
            case 'anime_subbed':
                $data = paginate(Anime::latest('latest', 'subbed',100), $size, $page);
                break;
            case 'anime_dubbed':
                $data = paginate(Anime::latest('latest', 'dubbed',100), $size, $page);
                break;
            case 'episodes_subbed':
                $data = paginate(Episode::latest('subbed', 100), $size, $page);
                break;
            case 'episodes_dubbed':
                $data = paginate(Episode::latest('dubbed', 100), $size, $page);
                break;
            default:
                $data = [];
        }

        return $response->withJson($data);
    }

}