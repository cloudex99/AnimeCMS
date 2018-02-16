<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018-02-09
 * Time: 10:02 PM
 */

use Cloud\Cache;

abstract class Config
{

    private static $settings = [];
    private static $key = 'settings';
    
    public static function init(){
        if(Cache::exists(static::$key)){
            self::$settings = Cache::fetch(static::$key);
        } else {
            self::$settings = json_decode(file_get_contents(BASE_DIR.'/data/settings.json'), true);
            Cache::save(static::$key, self::$settings);
        }
    }

    public static function get($key = null){
        if($key){
            return self::$settings[$key] ?? null;
        } else {
            return self::$settings;
        }
    }

    public static function set($key, $value){
        self::$settings[$key] = $value;
    }

    public static function remove($key){
        if(isset(self::$settings[$key]))
            unset(self::$settings[$key]);
    }

    public static function reload(){
        static::$settings = json_decode(file_get_contents(BASE_DIR.'/data/settings.json'), true);
        Cache::save(static::$key, self::$settings);
    }

    public static function save(){
        Cache::save(static::$key, self::$settings);
        $file = fopen('../data/settings.json', 'w');
        fwrite($file, json_encode(self::$settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        fclose($file);
    }

}