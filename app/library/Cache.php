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
    private static $redis = null;

    private static function memcached(){
        if(!isset(self::$memcached)){
            self::$memcached = new \Memcached();
            self::$memcached->addServer("127.0.0.1", 11211);
        }
        return self::$memcached;
    }

    private static function redis(){
        if(!isset(self::$redis)){
            self::$redis = new \Redis();
            self::$redis->connect('127.0.0.1');
        }
        return self::$redis;
    }

    public static function save(String $key, $data, $ttl = 3600*24*7){
        $key = CACHE_PREFIX.$key;
        $data = serialize($data);
        if(CACHE_MODE === 'memcached'){
            return self::memcached()->set($key, $data, $ttl);
        } elseif(CACHE_MODE === 'redis'){
            return self::redis()->set($key, $data, $ttl);
        }
        else {
            return apcu_store($key, $data, $ttl);
        }
    }

    public static function fetch(String $key){
        $key = CACHE_PREFIX.$key;
        if(CACHE_MODE === 'memcached'){
            $data = self::memcached()->get($key);
        } elseif(CACHE_MODE === 'redis'){
            $data = self::redis()->get($key);
        } else {
            $data = apcu_fetch($key);
        }

        return unserialize($data);
    }

    public static function delete(String $key){
        if(substr( $key, 0, strlen(CACHE_PREFIX) ) !== CACHE_PREFIX){
            $key = CACHE_PREFIX.$key;
        }
        echo "deleting $key<br>";
        if(CACHE_MODE === 'memcached'){
            return self::memcached()->delete($key);
        } elseif(CACHE_MODE === 'redis'){
            return self::redis()->delete($key);
        } else {
            return apcu_delete($key);
        }
    }

    public static function exists(String $key){
        $key = CACHE_PREFIX.$key;
        if(CACHE_MODE === 'memcached'){
            return self::memcached()->get($key);
        } elseif(CACHE_MODE === 'redis'){
            return self::redis()->exists($key);
        } else {
            return apcu_exists($key);
        }
    }

    public static function clearPrefix(String $prefix){
        $prefix = CACHE_PREFIX.$prefix;
        echo "clearing $prefix<br>";

        if(CACHE_MODE === 'memcached'){
            $keys = self::memcached()->getAllKeys();
            foreach ($keys as $index => $key) {
                if (strpos($key,$prefix) !== 0) {
                    unset($keys[$index]);
                } else {
                    self::memcached()->delete($key);
                }
            }

        } elseif(CACHE_MODE === 'redis'){
            self::redis()->delete(self::redis()->keys($prefix.'*'));
        }else {
            $keys = new \APCUIterator("/^$prefix/", APC_ITER_KEY);
            foreach ($keys as $key => $value) {
                self::delete($key);
            }
        }
    }

    public static function listKeys(){
        if(CACHE_MODE === 'memcached'){
            $keys = self::memcached()->getAllKeys();
        } elseif(CACHE_MODE === 'redis'){
            $keys = self::redis()->keys('*');
        } else {
            $keys = new \APCUIterator("", APC_ITER_KEY);
        }

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
                Cache::clearPrefix('list:');
                static::clearPrefix("episodes:");
            }
            static::delete("$anime->id:episodes");
            static::delete("anime-slug:$anime->slug");
            static::delete("anime-id:$anime->id");
        }
    }

}
