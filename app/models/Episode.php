<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-08-31
 * Time: 3:48 PM
 */

class Episode extends Model
{

    public $id;
    public $anime_id;
    public $slug;
    public $number;
    public $name;
    public $title;
    public $description;
    public $date;
    public $videos;
    public $image;
    public $next_id;
    public $previous_id;
    protected $anime;

    public function __construct($data, $save = true)
    {

        $this->id = $data->id;
        $this->anime_id = $data->anime_id;
        $this->slug = $data->slug;
        $this->number = $data->number ?? null;
        $this->name = $data->name;
        $this->title = $data->title ?? null;
        $this->description = $data->description ?? null;
        $this->date = $data->date;
        $this->videos = $data->videos ?? null;
        $this->image = $data->image;
        $this->url = $this->url();
        $this->next_id = $data->next ?? null;
        $this->previous_id = $data->previous ?? null;

        if(!Episode::exists("episode-id:$this->id") && $save) {
            Episode::save("episode-slug:$this->slug", "episode-id:$this->id");
            Episode::save("episode-id:$this->id", $this, DEFAULT_EXPIRE_TIME);
        }

    }

    //Gets the episode and stores in cache
    public static function get($params)
    {
        if(isset($params['id']) && $params['id']){
            $id = $params['id'];
            $key = "episode-id:$id";
            $url = API_URL."/episode/$id";
        }elseif(isset($params['slug']) && $params['slug']){
            $slug = Functions::filterSlug($params['slug']);
            $key = "episode-slug:$slug";
            $url = API_URL."/episode?slug=$slug";
        }else {
            return false;
        }

        if(Episode::exists($key)){
            $episode = Episode::fetch($key);
            return $episode;
        }else{
            if ($data = Functions::api_fetch($url)) {
                $episode = new Episode($data);
                return $episode;
            } else {
                return false;
            }
        }
    }

    //Gets the anime of the episode
    public function anime(){
        if(!isset($this->anime)){
            $this->anime = Anime::get(['id' => $this->anime_id]);
        }
        return $this->anime;
    }

    //Get the next episode
    public function next(){
        if($next = Episode::get(['id' => $this->next_id])){
            if(DUBBED_ONLY){
                if($next->hasDubbed()){
                    return $next;
                }
            } else {
                return $next;
            }
        }
        return false;
    }

    //Get the previous episode
    public function previous(){
        if($prev = Episode::get(['id' => $this->previous_id])){
            if(DUBBED_ONLY){
                if($prev->hasDubbed()){
                    return $prev;
                }
            } else {
                return $prev;
            }
        }
        return false;
    }

    //Call this for the episode name.
    public function name($suffix = false, $short = false)
    {

        $anime = $this->anime();
        $anime_name = $anime->name();
        $type = ucfirst(($anime->type == 'tv' ? 'episode' : strtoupper($anime->type)));

        if ($this->number === null) {
            if(empty($this->title)) {
                $name  = "$anime_name $type";
                if($short){
                    $name = "$type";
                }
            } else {
                $name = $this->title;
            }
        } else {
            $name = "$anime_name $type $this->number";
            if($short){
                $name = "$type $this->number";
            }
        }

        if(substri_count($name,$type) === 2){
            $name = str_lreplace($type ,'',$name);
        }

        if($suffix){
            $name = $name.' '.Functions::getSuffix($name);
        }

        return $name;
    }

    //Episode Url
    public function url($suffix = false){
        $slug = Functions::slugify($this->name());

        $url = SITE_URL.Config::get('episode')['base_url']."/{$this->id}-$slug";

        if($suffix === true){

            if(Functions::matchSlug('dubbed') && $this->hasDubbed())
                $url.=Config::get('url_suffix_dub');
            else
                $url.=Config::get('url_suffix_sub');
            return $url;
        }

        if(strtolower($suffix)==='subbed'){
            $url.=Config::get('url_suffix_sub');
        }elseif(strtolower($suffix)==='dubbed'){
            $url.=Config::get('url_suffix_dub');
        }elseif(DUBBED_ONLY){
            $url.=Config::get('url_suffix_dub');
        }

        return $url;
    }

    public function date(){
        return date('F j, Y', strtotime($this->date));
    }

    //Episode image, put your own default image, or change sizes. 352x220, 300x170, 160x100, 100x75
    public function image(){

        if($id = $this->has('host', 'trollvid')){
            $image = '//cdn.animeapi.com/images/1/'.$id.'_352x220.jpg';
        }elseif($id = $this->has('host', 'mp4upload')){
            $image = '//cdn.animeapi.com/images/2/'.$id.'_352x220.jpg';
        } else {
            $image = '//cdn.animeapi.com/images/default.jpg';
        }

        return $image;
    }

    //What type of episode to show
    public function setEpisodeType($type)
    {
        if ($type === 'subbed' || $type === 'dubbed') {
            $videos = [];
            foreach ($this->videos as $video) {
                if ($video->type === $type) {
                    $videos[] = $video;
                }
            }
            $this->videos = $videos;
        }
    }

    //Display subbed videos first
    public function subbedFirst()
    {
        $this->setDefault($host = null, 'subbed');
    }

    //Display dubbed videos first
    public function dubbedFirst()
    {
        $this->setDefault($host = null, 'dubbed');
    }

    public function setDefault($host, $type = null)
    {
        if(count($this->videos)){
            if (is_null($type)) {
                foreach ($this->videos as $k => $video) {
                    if ($video->host === $host) {
                        Functions::move_to_top($this->videos, $k);
                        break;
                    }
                }
            } else {
                foreach ($this->videos as $k => $video) {
                    if (!is_null($host)) {
                        if ($video->host === $host && $video->type === $type) {
                            Functions::move_to_top($this->videos, $k);
                            break;
                        }
                    } else {
                        if ($video->type === $type) {
                            Functions::move_to_top($this->videos, $k);
                            break;
                        }
                    }
                }
            }
        }
        return $this;
    }

    //Check if episode has any videos
    public function hasVideos(){
        if($this->videos !== null && !empty($this->videos) )
            return true;
        return false;
    }

    //Check if there is a subbed video
    public function hasSubbed(){
        return $this->has('type','subbed');
    }

    //Check if there is a dubbed video
    public function hasDubbed(){
        return $this->has('type','dubbed');
    }

    protected function has($property, $value){
        if($this->hasVideos()){
            foreach ($this->videos as $v){
                if($v->{$property}===$value)
                    return $v->id;
            }
        }
        return false;
    }

    //gets the latest episodes. subbed or dubbed
    public static function latest($type = 'dubbed', $limit = 100){
        $key = "episodes:latest:$limit";
        $episodes = [];
        if(Episode::exists($key)){
            $list = Episode::fetch($key);
        }else {
            $url = API_URL."/latest/episodes/$limit";
            $list = Functions::api_fetch($url);
            Episode::save($key, $list, RECENT_UPDATE_TIME);
        }
        foreach ($list->{$type} as $episode){
            $episode->type = $type;
            $episodes[] = new Episode($episode);
        }
        return $episodes;
    }

    //gets the latest episodes both subbed and dubbed together
    public static function latest_merged($limit = 100){
        $episodes = array_merge(Episode::latest('subbed', floor($limit/2)), Episode::latest('dubbed', ceil($limit/2)));
        usort($episodes, 'date_compare');
        return $episodes;
    }

    //gets the recent episodes.
    public static function recent($limit = 100){
        $key = "episodes:recent:$limit";
        $episodes = [];
        $episode_ids = [];

        if (Episode::exists($key)) {
            $episode_ids = Episode::fetch($key);
            foreach ($episode_ids as $episode_id) {
                $episode = Episode::get(['id' => $episode_id]);
                $episodes[] = $episode;
            }
        } else {
            $url = API_URL."/episode/latest/$limit";
            if ($recent = Functions::api_fetch($url)) {
                foreach ($recent as $episode) {
                    $episode_ids[] = $episode->id;
                    $episode = new Episode($episode);
                    $episodes[] = $episode;
                }
                Episode::save($key, $episode_ids, RECENT_UPDATE_TIME);
            }
        }
        return $episodes;
    }

    public function page_title(){
        return str_replace(
            array("{name}", "{english}", "{suffix}"),
            array($this->name(), $this->name->english ?? $this->name->default,Functions::getSuffix(true)),
            Config::get('episode')['title']
        );
    }

    public function meta_description(){
        return str_replace(
            array("{name}", "{english}", "{suffix}",'"'),
            array($this->name(), $this->name->english ?? $this->name->default,Functions::getSuffix(true),''),
            Config::get('episode')['desc']
        );
    }
}