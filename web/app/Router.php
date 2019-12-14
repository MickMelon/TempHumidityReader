<?php
namespace App;

use App\Controllers;
use App\Request;

class Router {
    /**
     * Gets the specified controller and action and acts on it to 
     * effectively start the app.
     * 
     * @return void
     */
    public function start() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $params = $this->getParams($requestMethod);
        $request = new Request($requestMethod, $params);

        $controller = $this->getRequestedController();
        $action = $this->getRequestedAction();

        $className = 'App\Controllers\\' . ucfirst($controller) . 'Controller';

        // Check to see if the class and method exist, if they do, call it.      
        if (class_exists($className, true)) {
            $controllerClass = new $className();
            if (method_exists($controllerClass, $action)) {
                $controllerClass->{ $action }($request);
                return;
            }    
        }

        // Just call the home controller index if none was specified
        $homeController = new Controllers\HomeController();
        $homeController->index($request);             
    }

    /**
     * Gets the POST or GET parameters if there are any.
     *
     * @param string $method The HTTP request method, POST or GET.
     * @return array
     */
    private function getParams($method) {
        $params = array();

        if ($method == 'POST') {
            foreach ($_POST as $key => $val) {
                $params[$key] = filter_input(INPUT_POST, $key);
            }
        } elseif ($method == 'GET') {
            foreach ($_GET as $key => $val) {
                $params[$key] = filter_input(INPUT_GET, $key);
            }
        }

        return $params;
    }

    /**
     * Get the controller name from the GET parameter.
     *
     * @return string
     */
    private function getRequestedController() {
        return isset($_GET['c']) ? filter_input(INPUT_GET, $_GET['c']) : 'home';
    }

    /**
     * Get the action name from the GET parameter.
     *
     * @return string
     */
    private function getRequestedAction() {
        return isset($_GET['a']) ? filter_input(INPUT_GET, $_GET['a']) : 'index';
    }
}