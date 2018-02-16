<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-08-31
 * Time: 3:48 PM
 */

use \Cloud\Cache;

class Model extends Cache
{
    public static function fetch(String $key)
    {
        if(strpos($key, '-slug:') !== false ){
            $data = parent::fetch(parent::fetch($key));
        }else{
            $data = parent::fetch($key);
        }
        return $data;
    }


}
