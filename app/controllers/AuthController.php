<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-12-09
 * Time: 4:38 PM
 */
use Firebase\JWT\JWT;

class AuthController extends Controller
{

    public function login($request, $response, $args){

        $this->view->render($response, TEMPLATE_BASE.'login.php', [
            'title' => 'Login',
            'scripts' => []
        ]);

        return $response;
    }

    public function loginHandler($request, $response, $args){
        if(isset($_POST['username']) && $_POST['username'] === Config::get('admin_username') && isset($_POST['password']) && password_verify($_POST['password'], Config::get('admin_password'))){
            $_SESSION['admin'] = true;
            $redir = $_SESSION['login_redirect'];
            unset($_SESSION['login_redirect']);
            return $response->withRedirect($redir);
        } else {
            return $response->withRedirect('/login?error=1');
        }
    }

    public function logout($request, $response, $args){

        if(isset($_SESSION['user'])){
            unset($_SESSION['user']);
        }
        if(isset($_SESSION['admin'])){
            unset($_SESSION['admin']);
        }
        if(isset($_COOKIE["AUTH_X_TOKEN"])){
            unset($_COOKIE['AUTH_X_TOKEN']);
            setcookie ("AUTH_X_TOKEN", "", time() - 3600, '/',parse_url(SITE_URL, PHP_URL_HOST), true, true);
        }

        return $response->withRedirect(SITE_URL);
    }

    public function connect($request, $response, $args){
        $key = LOGIN_SECRET;

        if(isset($_GET['token'])){
            $jwt = $_GET['token'];
            $decoded = JWT::decode($jwt, $key, array('HS256'));

            if($decoded->data->remember){
                setcookie( "AUTH_X_TOKEN", $jwt, strtotime( '+30 days' ), '/', parse_url(SITE_URL, PHP_URL_HOST), true, true);
            } else {
                setcookie( "AUTH_X_TOKEN", $jwt, strtotime( '+1 hour' ), '/', parse_url(SITE_URL, PHP_URL_HOST), true, true);
            }

            $_SESSION['user'] = serialize($decoded->data);

            $url = $_SESSION['login_redirect'] ?? '/';

            unset($_SESSION['login_redirect']);

            if($url == ''){
                $url = '/';
            }

            $response = $response->withRedirect($url);
        }

        return $response;
    }

}