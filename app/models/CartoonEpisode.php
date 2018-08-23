<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-08-19
 * Time: 8:34 PM
 */

use Cloud\Cache;

class CartoonEpisode extends Model
{
    public $id;
    public $cartoon_id;
    public $slug;
    public $season;
    public $number;
    public $title;
    public $description;
    public $date;
    public $videos;
    public $image;
    public $next_id;
    public $previous_id;
    protected $cartoon = null;
    protected static $prefix = ['id' => 'ce_id:', 'list' => 'ce_list:'];

    public function __construct($data, $save = true)
    {

        $this->id = $data->id;
        $this->cartoon_id = $data->cartoon_id;
        $this->slug = $data->slug;
        $this->season = $data->season ?? null;
        $this->number = $data->number ?? null;
        $this->title = $data->title ?? null;
        $this->description = $data->description ?? null;
        $this->date = $data->date;
        $this->videos = $data->videos ?? null;
        $this->image = $data->image;
        $this->url = $this->url();
        $this->next_id = $data->next ?? null;
        $this->previous_id = $data->previous ?? null;

        $key = self::$prefix['id'].$this->id;
        if(!Cache::exists($key) && $save) {
            Cache::save($key, $this, DEFAULT_EXPIRE_TIME);
        }

    }

    //Gets the episode and stores in cache
    public static function get($id)
    {
        $key = self::$prefix['id'].$id;
        $url = API_URL."/cartoon/episode/$id";

        if(Cache::exists($key)){
            $episode = Cache::fetch($key);
            return $episode;
        }else{
            if ($data = Functions::api_fetch($url)) {
                $episode = new CartoonEpisode($data);
                return $episode;
            } else {
                return null;
            }
        }
    }

    //Gets the cartoon of the episode
    public function cartoon(){
        if(is_null($this->cartoon)){
            $this->cartoon = Cartoon::get($this->cartoon_id);
        }
        return $this->cartoon;
    }

    //Get the next episode
    public function next(){
        if($next = CartoonEpisode::get($this->next_id)){
            return $next;
        }
        return false;
    }

    //Get the previous episode
    public function previous(){
        if($prev = CartoonEpisode::get($this->previous_id)){
            return $prev;
        }
        return false;
    }

    //Call this for the episode name.
    public function name($suffix = false, $short = false)
    {

        $cartoon = $this->cartoon();

        $type = ucfirst(($cartoon->type == 'tv' ? 'episode' : strtoupper($cartoon->type)));

        if ($this->number === null) {
            if(empty($this->title)) {
                $name  = "$cartoon->title $type";
                if($short){
                    $name = "$type";
                }
            } else {
                $name = $this->title;
            }
        } else {
            $season = '';
            if(!is_null($this->season) && stripos($cartoon->title, 'season') === false){
                $season = "Season $this->season ";
            }
            $name = "$cartoon->title $season$type $this->number";
            if($short){
                $name = "$type $this->number";
            }
        }

        if(substri_count($name,$type) === 2){
            $name = str_lreplace($type ,'',$name);
        }

        return $name;
    }

    public function date(){
        return date('F j, Y', strtotime($this->date));
    }

    //Episode image, put your own default image, or change sizes. 352x220, 300x170, 160x100, 100x75
    public function image(){
        $image = '//cdn.animeapi.com/images/3/'.$this->id.'_352x220.jpg';
        return $image;
    }
    //Episode Url
    public function url($suffix = false){

        $slug = Functions::slugify($this->name());
        $url = SITE_URL.Config::get('cartoon_episode')['base_url']."/{$this->id}-$slug";

        return $url;
    }

    public function page_title(){
        return str_replace(
            array("{name}"),
            array($this->name()),
            Config::get('episode')['title']
        );
    }

    public function meta_description(){
        return str_replace(
            array("{name}"),
            array($this->name()),
            Config::get('episode')['desc']
        );
    }
}