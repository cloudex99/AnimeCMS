<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:26 PM
 */

use Cloud\Cache;


class IndexController extends Controller
{

    public function home($request, $response, $args){

        $this->view->render($response, TEMPLATE_BASE.'index.php', [
            'title' => Config::get('home_title'),
            'description' => Config::get('home_desc'),
            'scripts' => Config::get('home_scripts')
        ]);

        return $response;
    }

    public function settings($request, $response, $args){

        if($request->isGet()){

            $this->view->render($response, TEMPLATE_BASE.'settings.php', [
                'title' => 'SETTINGS',
                'description' => 'EDIT SETTINGS',
                'scripts' => []
            ]);

            return $response;
        }
        if($request->isPost()){
                foreach ($_POST as $key => $value){
                    if($key === 'admin_password' && $value !== Config::get('admin_password')){
                        Config::set($key, password_hash($value, PASSWORD_DEFAULT));
                    }elseif(strpos($key,'base_url')!==false){
                        Config::set($key, '/'.trim($value,'/'));
                    } else {
                        Config::set($key, $value);
                    }
                }
                Config::save();
            return $response->withRedirect('/settings');
        } else {
            die();
        }
    }

    public function reloadSettings($request, $response, $args){
        Config::reload();
        return $response->withRedirect('/settings');
    }

    public function generateSitemap($request, $response, $args){
        exec("php sitemaps/generate.php > /dev/null 2>&1 &");
        $_SESSION['msg'] = 'Sitemap is being generated. Might take a while.';
        return $response->withRedirect('/settings');
    }

    public function purge(){
        Functions::listen();
    }

}