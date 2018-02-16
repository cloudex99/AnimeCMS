<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-09-01
 * Time: 11:44 PM
 */
namespace Cloud;

class Cache
{

    private static $memcached = null;

    private static function mem(){
        if(!isset(self::$memcached)){
            self::$memcached = new \Memcached();
            self::$memcached->addServer("127.0.0.1", 11211);
        }
        return self::$memcached;
    }

    public static function save(String $key, $data, $ttl = 3600*24*7){
        //return self::mem()->set($key, $data, $ttl);
        return apcu_store(CACHE_PREFIX.$key, serialize($data), $ttl);
    }

    public static function fetch(String $key){
        //return self::mem()->get($key);
        return unserialize(apcu_fetch(CACHE_PREFIX.$key));
    }

    public static function delete(String $key){
        //return self::mem()->delete($key);
        return apcu_delete(CACHE_PREFIX.$key);
    }

    public static function exists(String $key){
        //return self::mem()->get($key);
        return apcu_exists(CACHE_PREFIX.$key);
    }

    public static function clearPrefix($prefix){
        $keys = new \APCUIterator("/^$prefix/", APC_ITER_KEY);
        foreach ($keys as $key => $value) {
            self::delete($key);
        }
    }

    public static function listKeys(){
        $keys = new \APCUIterator("", APC_ITER_KEY);
        foreach ($keys as $key => $value) {
            echo $key.'<br>';
        }
    }

    public static function deleteEpisode($id){
        if($episode = \Episode::get(['id' => $id])){
            if($episode->next())
                Cache::delete("episode-id:$episode->next_id");
            if($episode->previous())
                Cache::delete("episode-id:$episode->previous_id");
            Cache::clearPrefix('episodes:');
            Cache::delete("$episode->anime_id:episodes");
            static::delete("episode-slug:$episode->slug");
            static::delete("episode-id:$episode->id");
        }
    }

    public static function deleteAnime($id, $purge = false){
        if($anime = \Anime::get(['id' => $id])){
            if($purge){
                $episode_ids = static::fetch("$anime->id:episodes");
                if(count($episode_ids)){
                    foreach ($episode_ids as $episode_id){
                        static::delete("episode-id:$episode_id");
                        static::delete("episode-slug:$episode_id");
                    }
                }
                Cache::clearPrefix('list');
                static::clearPrefix("episodes:");
            }
            static::delete("$anime->id:episodes");
            static::delete("anime-slug:$anime->slug");
            static::delete("anime-id:$anime->id");
        }
    }

}
