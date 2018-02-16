<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017-11-19
 * Time: 3:30 PM
 */

use \Interop\Container\ContainerInterface;

class Controller
{
    protected $ci;
    protected $view;

    // constructor receives container instance
    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->view = $ci->get('view');
    }

    public function error404($request, $response, $args) {
        $this->view->render($response, TEMPLATE_BASE.'404.php', [
            'title' => '404 Not Found',
            'scripts' => []
        ]);
        return $response = $response->withStatus(404);
    }

}