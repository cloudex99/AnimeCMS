<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-08-31
 * Time: 3:48 PM
 */

use \Cloud\Cache;

class Anime extends Model
{
    public $id;
    public $slug;
    public $title;
    public $image;
    public $synopsis;
    public $english;
    public $japanese;
    public $synonyms;
    public $type;
    public $total;
    public $status;
    public $date;
    public $aired;
    public $premiered;
    public $duration;
    public $rating;
    public $genres;
    public $related;
    private $episodes = [];
    private $_reversed = false;

    public function __construct($data)
    {
        $this->id = $data->id;
        $this->slug = $data->slug;
        $this->title = $data->title;
        $this->image = $data->image;
        $this->synopsis = $data->synopsis;
        @$this->english = $data->english ?: $data->title;
        $this->japanese = $data->japanese;
        $this->synonyms = $data->synonyms;
        $this->type = $data->type;
        $this->total = $data->total;
        $this->status = $data->status;
        $this->date = $data->date;
        $this->aired = $data->aired;
        $this->premiered = $data->premiered;
        $this->duration = $data->duration;
        $this->rating = $data->rating;
        @$this->genres = $data->genres ?: null;
        @$this->related = $data->related_anime ?: null;
        $this->url = $this->url();

        if (!Cache::exists("anime-id:$this->id")) {
            Cache::save("anime-slug:$this->slug", "anime-id:$this->id");
            Cache::save("anime-id:$this->id", $this);
        }
    }

    public function iterable()
    {
        return ['title' => $this->title, 'english' => $this->english, 'synonyms' => $this->synonyms, 'japanese' => $this->japanese, 'type' => $this->type, 'total' => $this->total, 'status' => $this->status, 'aired' => $this->aired, 'premiered' => $this->premiered, 'genres' => $this->genres, 'duration' => $this->duration, 'rating' => $this->rating];
    }

    //Get the anime and store it in cache
    public static function get($params)
    {

        if (isset($params['id'])) {
            $id = $params['id'];
            $key = "anime-id:$id";
            $url = API_URL."/anime/$id";
        } elseif (isset($params['slug'])) {
            $slug = Functions::filterSlug($params['slug']);
            $key = "anime-slug:$slug";
            $url = API_URL."/anime?slug=$slug";
        } else {
            return false;
        }

        if (Cache::exists($key)) {
            $anime = Anime::fetch($key);
            return $anime;

        } else {
            if ($data = Functions::api_fetch($url)) {
                $anime = new Anime($data);
                return $anime;
            } else {
                return false;
            }
        }

    }

    //Get the name of the anime
    public function name($suffix = false)
    {
        if (DUBBED_ONLY)
            $name = $this->english ?: $this->title;
        else
            $name = $this->title;

        if ($suffix) {
            $name = "$name ".Functions::getSuffix(true);
        }

        return $name;
    }

    //Anime Url
    public function url($suffix = false)
    {
        $slug = Functions::slugify($this->name());
        $url = SITE_URL.Config::get('anime')['base_url']."/{$this->id}-$slug";

        if(strtolower($suffix)==='subbed'){
            $url.=Config::get('url_suffix_sub');
        }elseif(strtolower($suffix)==='dubbed'){
            $url.=Config::get('url_suffix_dub');
        }elseif(DUBBED_ONLY){
            $url.=Config::get('url_suffix_dub');
        }

        return $url;
    }

    //Check if anime has episodes
    public function hasEpisodes()
    {
        $this->getEpisodes();
        if ($this->episodes !== null && !empty($this->episodes))
            return true;
        return false;
    }

    public function type()
    {
        return ucfirst(($this->type == 'tv' ? 'episode' : strtoupper($this->type)));
    }

    //Get the episodes of the anime and store in cache
    public function getEpisodes()
    {

        $episodes = [];
        $episode_ids = [];
        $key = "$this->id:episodes";

        if (Cache::exists($key)) {
            $episode_ids = Anime::fetch($key);
            foreach ($episode_ids as $episode_id) {
                $episode = Episode::get(['id' => $episode_id]);
                if(DUBBED_ONLY && $episode->hasDubbed()){
                    $episodes[] = $episode;
                } else {
                    $episodes[] = $episode;
                }
            }
        } else {

            $url = API_URL."/anime/$this->id/episodes";
            if ($data = Functions::api_fetch($url)) {
                foreach ($data as $episode) {
                    $episode_ids[] = $episode->id;
                    $episode = new Episode($episode);
                    if(DUBBED_ONLY && $episode->hasDubbed()){
                        $episodes[] = $episode;
                    } else {
                        $episodes[] = $episode;
                    }
                }
                if($this->status === 'ongoing'){
                    Cache::save($key, $episode_ids, DEFAULT_EXPIRE_TIME);
                } else {
                    Cache::save($key, $episode_ids);
                }
            }
        }

        return $episodes;
    }

    //Sets the episode order. asc or desc
    public function setEpisodeOrder($order)
    {
        if ($order === 'asc' && $this->_reversed === false) {
            $this->episodes = array_reverse($this->episodes);
            $this->_reversed = true;
        }
        if ($order === 'desc' && $this->_reversed === true) {
            $this->episodes = array_reverse($this->episodes);
            $this->_reversed = false;
        }
    }

    //Gets a list of anime based on a query. Ex: status=ongoing&lang=eng
    //Leave blank for full anime list
    public static function query($query = null, $args = null)
    {
        $id_list = [];
        $anime_list = [];

        $has_query = false;

        if (isset($query) && !empty($query)) {
            if(DUBBED_ONLY){
                $query.='&lang=eng';
            }
            $key = $query;
        } else {
            $key = $query ?? 'all-anime';
        }

        $key = "list:$key";

        if (!is_null($query)) {
            $has_query = true;
        }

        if (Cache::exists($key)) {
            $id_list = Anime::fetch($key); //retrieve id list from cache
            foreach ($id_list as $id) {
                if ($anime = Anime::get(['id' => $id])) { //get anime from cache
                    $anime_list[] = $anime;
                }
            }
        } else {
            $url = API_URL. "/anime?$query";
            if ($list = Functions::api_fetch($url)) {
                //if we have a query, save the ids as a list
                if ($has_query) {
                    foreach ($list as $data) {
                        $id_list[] = $data->id;
                        $anime_list[] = Anime::get(['id' => $data->id]);  //get the anime from cache
                    }
                } else {
                    //else instantiate new anime from full list and save to cache
                    foreach ($list as $data) {
                        $id_list[] = $data->id;
                        $anime_list[] = new Anime($data);
                    }
                }
                Cache::save($key, $id_list);
            }
        }

        if(DUBBED_ONLY && is_null($query)){
            return Anime::query('lang=eng');
        }

        return $anime_list;
    }

    //Returns a list in a multidimensional array in A-Z
    public static function letters(&$list, $l = null)
    {
        $anime_letters = [];//create a new array
        foreach ($list as $item) {
            $letter = strtoupper($item->name()[0]);//get the first letter

            if (!ctype_alpha($letter)) {//test if its letter
                $anime_letters['#'][] = $item;

            } else if (isset($anime_letters[$letter]) && count($anime_letters[$letter])) {//test if the letter is in the array
                $anime_letters[$letter][] = $item;//save the value
            } else {
                $anime_letters[$letter] = [$item];
            }
        }

        if ($l) {
            if (isset($anime_letters[$l]))
                return $anime_letters[$l];
            else
                return [];
        }

        return $anime_letters;
    }

    //Gets search results based on keyword
    public static function search($keyword)
    {
        $keyword = urlencode($keyword);
        return Anime::query("search=$keyword");
    }

    //Searches for anime including given genres. use comma separated. action,adventure,comedy
    //Check https://api.animenetwork.net/genre for list of valid genres
    public static function genres($include = null)
    {
        if(is_null($include))
            return ["action","adventure","cars","comedy","dementia","demons","drama","ecchi","fantasy","game","harem","historical","horror","josei","kids","magic","martial arts","mecha","military","music","mystery","parody","police","psychological","romance","samurai","school","sci-fi","seinen","shoujo","shoujo ai","shounen","shounen ai","slice of life","space","sports","super power","supernatural","thriller","vampire","yuri"];
        return Anime::query("genre=$include");
    }

    public function page_title(){
        return str_replace(
            array("{name}"),
            array($this->name()),
            Config::get('anime')['title']
        );
    }

    public function meta_description(){
        return str_replace(
            array("{name}", "{synonyms}","{synopsis}","{english}",'"'),
            array($this->name(), $this->synonyms,$this->synopsis,$this->english,""),
            Config::get('anime_page_desc')
        );
    }

    public function display_information($return = false)
    {
        $out = '';
        foreach ($this->iterable() as $field => $value) {
            $field = ucfirst($field);
            $value = ucfirst($value);
            if ($return) {
                if ($value) {
                    $out .= "<p><small><b>$field:</b> $value</small></p>";
                }
            } else {
                if ($value) {
                    echo "<p><b>$field:</b> $value</p>";
                }
            }
        }
        return $out;
    }

    //gets the latest anime. kind can be latest or ongoing, type can be subbed or dubbed.
    public static function latest($kind = 'latest', $type = 'subbed', $limit = 15)
    {
        $animes = [];

        $key = "list:latest-anime:$limit";
        if (Cache::exists($key)) {
            $list = Anime::fetch($key);
        } else {
            $url = API_URL."/latest/anime/$limit";
            $list = Functions::api_fetch($url);
            Cache::save($key, $list, RECENT_UPDATE_TIME);
        }

        foreach ($list->{$kind}->{$type} as $anime){
            $animes[] = Anime::get(['id' => $anime->id]);
        }

        return $animes;
    }

    //gets the latest episodes both subbed and dubbed together
    public static function latest_merged($limit = 100){
        $latest = array_merge(Anime::latest('latest','dubbed', ceil($limit/2)), Anime::latest('latest','subbed', floor($limit/2)));
        usort($latest, 'date_compare');
        return $latest;
    }

    public static function ongoing($dubbed = false){

        if($dubbed) {
            $id_list = [];
            $anime_list = [];

            $key = 'list:ongoing-dub';
            if(Cache::exists($key)){
                $id_list = Anime::fetch($key);
                foreach ($id_list as $id){
                    if($anime = Anime::get(['id' => $id])){
                        $anime_list[] = $anime;
                    }
                }
            }else {
                $url = API_URL."/ongoing";
                $list = Functions::api_fetch($url);
                foreach($list as $data){
                    $id_list[] = $data->id;
                    $anime_list[] = Anime::get(['id' => $data->id]);
                }
                Cache::save($key, $id_list, DEFAULT_EXPIRE_TIME);
            }
            usort($anime_list, 'compareByName');
            return $anime_list;
        } else {

            $anime_list = Anime::query('status=ongoing');

            return $anime_list;
        }
    }

    public static function random(){
        $animes = Anime::query();
        return $animes[array_rand($animes)];
    }

}