<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-09-05
 * Time: 9:58 PM
 */


use \Cloud\Cache;

abstract class Functions
{

    private static $detect;

    public static function detect(){
        if(!isset(static::$detect)){
            static::$detect = new Mobile_Detect();
        }
        return static::$detect;
    }

    /**
     * Convert an array into a stdClass()
     *
     * @param   array   $array  The array we want to convert
     *
     * @return  object
     */
    public static function arrayToObject($array)
    {
        // First we convert the array to a json string
        $json = json_encode($array);

        // The we convert the json string to a stdClass()
        $object = json_decode($json);

        return $object;
    }

    /**
     * Convert a object to an array
     *
     * @param   object  $object The object we want to convert
     *
     * @return  array
     */
    public static function objectToArray($object)
    {
        // First we convert the object into a json string
        $json = json_encode($object);

        // Then we convert the json string to an array
        $array = json_decode($json, true);

        return $array;
    }

    public static function move_to_top(&$array, $key) {
        $temp = array($key => $array[$key]);
        unset($array[$key]);
        $array = $temp + $array;

        $array = array_values($array);
    }

    public static function matchSlug($type, $slug = null){
        if(is_null($slug)){
            $slug = Functions::urlSegment();
        }
        $match = false;
        if(preg_match('(-english-subbed|-english-sub|-eng-sub|-sub|-subbed)', $slug) === 1){
            $match = 'subbed';
        }
        if(preg_match('(-english-dubbed|-english-dub|-eng-dub|-dub|-dubbed)', $slug) === 1) {
            $match = 'dubbed';
        }
        if(strtolower($type) === $match){
            return true;
        }
        return false;
    }

    public static function getSuffix($nice = false){
        $slug = Functions::urlSegment();
        if(preg_match('(-english-dubbed|-english-dub|-eng-dub|-dub|-dubbed)', $slug, $matches) === 1) {
            $suffix = $matches[0];
        } elseif(preg_match('(-english-subbed|-english-sub|-eng-sub|-sub|-subbed)', $slug, $matches) === 1){
            $suffix = $matches[0];
        }else {
            $suffix =  '';
        }
        if($nice && $matches){
            $suffix = ucwords(trim(str_replace('-',' ',$matches[0])));
        }
        return $suffix;
    }

    //Filter the slug
    public static function filterSlug($slug){
        return trim(str_replace(array('-english-dubbed', '-english-dub', '-eng-dubbed', '-eng-dub', '-dub', '-dubbed', '-english-subbed', '-english-sub', '-eng-subbed', '-eng-sub', '-sub', '-subbed'), '', $slug),'/');
    }

    public static function urlSegment($i = false){
        $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        if(!$i){
            $i = count($uri_segments)-1;
        }
        return $uri_segments[$i];
    }

    //Listen to the api for updates to clear local cache
    public static function listen(){
        if(isset($_POST['api_key']) && $_POST['api_key'] === API_KEY){
            if(isset($_POST['anime'])){
                $id = $_POST['anime'][0];
                $action = $_POST['anime'][1];
                if($action == 'created'){
                    Cache::clearPrefix('list');
                }elseif ($action == 'updated'){
                    if($anime = Anime::get(['id' => $id])){
                        Cache::deleteAnime($id);
                        Cache::delete("list:status=ongoing");
                    }
                }else{
                    if($anime = Anime::get(['id' => $id])){
                        Cache::deleteAnime($id, true);
                    }
                }
                Anime::query();
            }
            if(isset($_POST['episode'])){
                $id = $_POST['episode'][0];
                //$action = $_POST['episode'][1];
                Cache::deleteEpisode($id);
            }
        }
    }

    public static function createKey($parameters)
    {
        $uniqueKey = [];

        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $uniqueKey[] = $key . ":" . $value;
            } elseif (is_array($value)) {
                $uniqueKey[] = $key . ":[" . self::createKey($value) . "]";
            }
        }

        return join(",", $uniqueKey);
    }

     /**
      * @param $url
      * @param $decode
      * @return mixed
      * Fetches information from the api.
      */
     public static function api_fetch($url, $decode = false)
     {

         $options = array(
             'http' => array(
                 'method' => 'GET'
             ),
         );

         $context = stream_context_create($options);

         @$result = json_decode(file_get_contents($url, false, $context), $decode);

         if($result != null && $result->status === 'FOUND'){
             return $result->data;
         }

         return false;
     }

    public static function slugify($slug)
    {
        // replace non letter or digits by -
        $slug = preg_replace('~[^\\pL\d]+~u', '-', $slug);
        // trim
        $slug = trim($slug, '-');
        // transliterate
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        // lowercase
        $slug = strtolower($slug);
        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        if (empty($slug))
        {
            return false;
        }

        return $slug;
    }

    public static function get_id_from_slug($slug){
         return explode('-',$slug)[0];
    }

}