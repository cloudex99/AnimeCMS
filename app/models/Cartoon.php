<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-22
 * Time: 5:10 PM
 */

use Cloud\Cache;

class Cartoon
{

    public $id;
    public $slug;
    public $title;
    public $image;
    public $synopsis;
    public $type;
    public $status;
    public $date;
    public $genres;
    private $episodes = [];
    private $_reversed = false;

    protected static $prefix = ['id' => 'c_id:', 'list' => 'c_list:'];

    public function __construct($data)
    {

        $this->id = $data->id;
        $this->slug = $data->slug;
        $this->title = $data->title;
        $this->image = $data->image;
        $this->synopsis = $data->synopsis;
        $this->type = $data->type;
        $this->status = $data->status;
        $this->date = $data->date;
        $this->genres = $data->genres ?: null;
        $this->url = $this->url();

        $key = self::$prefix['id'].$this->id;
        if(!Cache::exists($key)) {
            Cache::save($key, $this, DEFAULT_EXPIRE_TIME);
        }
    }


    //Gets the cartoon and stores in cache
    public static function get($id)
    {
        $key = self::$prefix['id'].$id;
        $url = API_URL."/cartoon/$id";

        if(Cache::exists($key)){
            $cartoon = Cache::fetch($key);
            return $cartoon;
        }else{
            if ($data = Functions::api_fetch($url)) {
                $cartoon = new Cartoon($data);
                return $cartoon;
            } else {
                return false;
            }
        }
    }

    //Cartoon Url
    public function url($suffix = false)
    {
        $slug = Functions::slugify($this->title);
        $url = SITE_URL.Config::get('cartoon')['base_url']."/{$this->id}-$slug";
        return $url;
    }

    //Check if cartoon has episodes
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

    public function name(){
        return $this->title;
    }
    //Get the episodes of the cartoon and store in cache
    public function getEpisodes()
    {
        $episodes = [];
        $episode_ids = [];
        $key = self::$prefix['id'].$this->id.":episodes";

        if (Cache::exists($key)) {
            $episode_ids = Cache::fetch($key);
            foreach ($episode_ids as $episode_id) {
                $episode = CartoonEpisode::get($episode_id);
                if(DUBBED_ONLY && $episode->hasDubbed()){
                    $episodes[] = $episode;
                } else {
                    $episodes[] = $episode;
                }
            }
        } else {
            $url = API_URL."/cartoon/$this->id/episodes";
            if ($data = Functions::api_fetch($url)) {
                foreach ($data as $episode) {
                    $episode_ids[] = $episode->id;
                    $episode = new CartoonEpisode($episode);
                    $episodes[] = $episode;
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

    //Gets a list of cartoon based on a query. Ex: status=ongoing&type=movie
    //Leave blank for full cartoon list
    public static function query($query = null)
    {
        $id_list = [];
        $cartoon_list = [];

        $has_query = false;

        if (isset($query) && !empty($query)) {
            $key = $query;
        } else {
            $key = $query ?? 'all';
        }

        $key = self::$prefix['list']."$key";

        if (!is_null($query)) {
            $has_query = true;
        }

        if (Cache::exists($key)) {
            $id_list = Cache::fetch($key); //retrieve id list from cache
            foreach ($id_list as $id) {
                if ($cartoon = Cartoon::get($id)) { //get cartoon from cache
                    $cartoon_list[] = $cartoon;
                }
            }
        } else {
            $url = API_URL. "/cartoon?$query";
            if ($list = Functions::api_fetch($url)) {
                //if we have a query, save the ids as a list
                if ($has_query) {
                    foreach ($list as $data) {
                        $id_list[] = $data->id;
                        $cartoon_list[] = Cartoon::get($data->id);  //get the cartoon from cache
                    }
                } else {
                    //else instantiate new cartoon from full list and save to cache
                    foreach ($list as $data) {
                        $id_list[] = $data->id;
                        $cartoon_list[] = new Cartoon($data);
                    }
                }
                Cache::save($key, $id_list);
            }
        }
        
        return $cartoon_list;
    }

    //Returns a list in a multidimensional array in A-Z
    public static function letters(&$list, $l = null)
    {
        $cartoon_letters = [];//create a new array
        foreach ($list as $item) {
            $letter = strtoupper($item->title[0]);//get the first letter

            if (!ctype_alpha($letter)) {//test if its letter
                $cartoon_letters['#'][] = $item;

            } else if (isset($cartoon_letters[$letter]) && count($cartoon_letters[$letter])) {//test if the letter is in the array
                $cartoon_letters[$letter][] = $item;//save the value
            } else {
                $cartoon_letters[$letter] = [$item];
            }
        }

        if ($l) {
            if (isset($cartoon_letters[$l]))
                return $cartoon_letters[$l];
            else
                return [];
        }

        return $cartoon_letters;
    }

    //Gets search results based on keyword
    public static function search($keyword)
    {
        $keyword = urlencode($keyword);
        return Cartoon::query("search=$keyword");
    }

    //Searches for cartoon including given genres. use comma separated. action,adventure,comedy
    //Check https://api.cartoonnetwork.net/genre for list of valid genres
    public static function genres($include = null)
    {
        return Cartoon::query("genre=$include");
    }

    public function page_title(){
        return str_replace(
            array("{name}"),
            array($this->title),
            Config::get('cartoon')['title']
        );
    }

    public function meta_description(){
        return str_replace(
            array("{name}","{synopsis}",'"'),
            array($this->title, $this->synopsis,""),
            Config::get('cartoon_page_desc')
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
    
    public static function random(){
        $cartoons = Cartoon::query();
        return $cartoons[array_rand($cartoons)];
    }
}