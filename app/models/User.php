<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-20
 * Time: 1:38 AM
 */
use \Firebase\JWT\JWT;

class User
{
    private static $id = false;
    private static $username = false;
    private static $email = false;
    private static $usergroupid = false;
    private static $membergroupids = false;
    private static $securitytoken = false;
    private static $token_name = 'security_token';
    private static $is_admin = false;
    private static $is_supermod = false;
    private static $is_staff = false;
    private static $is_vip = false;
    private static $vip_type = false;

    private function __construct()
    {

    }

    public static function init(){
        if(isset($_COOKIE["AUTH_X_TOKEN"])){
            $token = $_COOKIE['AUTH_X_TOKEN'];
            try {
                $decoded = JWT::decode($token, LOGIN_SECRET, array('HS256'));
                $_SESSION['user'] = serialize($decoded->data);
            }
            catch (\Firebase\JWT\ExpiredException $e) {
                header("Location: /logout");
            }catch ( Exception $e ){
                header("Location: /logout");
            }
        }
        if(isset($_SESSION['user'])) {
            $user = unserialize($_SESSION['user']);
            static::$id = $user->userid;
            static::$username = $user->username;
            static::$email = $user->email;
            static::$usergroupid = $user->usergroupid;
            static::$membergroupids = $user->membergroupids;
            static::$securitytoken = $user->securitytoken;
            static::$is_admin = $user->is_admin;
            static::$is_supermod = $user->is_supermod;
            static::$is_staff = $user->is_staff;
            static::$is_vip = $user->is_vip;
            static::$vip_type = $user->vip_type;
        }
        if(isset($_SESSION['admin'])) {
            static::$id = 1;
            static::$username = Config::get('admin_username');
            static::$is_admin = true;
            static::$securitytoken = md5(uniqid(rand(), true));
        }
    }

    public static function getUserGroupID(){
        return static::$id;
    }

    public static function getUserID(){
        return static::$id;
    }

    public static function getUsername(){
        return static::$username;
    }

    public static function isStaff(){
        return static::$is_staff;
    }

    public static function isAdmin(){
        return static::$is_admin;
    }

    public static function isVip(){
        return static::$is_vip;
    }
    public static function vipType(){
        return static::$vip_type;
    }

    public static function isLoggedIn(){
        if(static::$id && static::$id != null)
            return true;
        return false;
    }

    public static function hasAccess(){
        return (static::$is_admin || static::$is_supermod) ? true : false;
    }

    public static function getUserInfo(){

    }

    public static function getSecurityToken(){
        return (strtolower(static::$securitytoken)==='guest') ? '' : static::$securitytoken;
    }

    public static function getTokenName(){
        return static::$token_name;
    }

    public static function validateToken(){
        if(isset($_REQUEST[self::getTokenName()]) && !empty($_REQUEST[self::getTokenName()]) && $_REQUEST[self::getTokenName()] === User::getSecurityToken()){
            return true;
        }
        return false;
    }
}