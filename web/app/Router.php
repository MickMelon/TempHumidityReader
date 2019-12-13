<?php
namespace App;

use App\Controllers;

class Router {
    /**
     * Gets the specified controller and action and acts on it to 
     * effectively start the app.
     * 
     * @return void
     */
    public function start() {
        $controller = $this->getControllerFromParam();
        $action = $this->getActionFromParam();

        $className = 'App\Controllers\\' . ucfirst($controller) . 'Controller';

        // Check to see if the class and method exist, if they do, call it.      
        if (class_exists($className, true)) {
            $controllerClass = new $className();
            if (method_exists($controllerClass, $action)) {
                $controllerClass->{ $action }();
                return;
            }    
        }

        // Just call the home controller index if none was specified
        $homeController = new Controllers\HomeController();
        $homeController->index();             
    }

    /**
     * Get the controller name from the GET parameter.
     *
     * @return string
     */
    private function getControllerFromParam() {
        return isset($_GET['c']) ? strtolower($_GET['c']) : 'home';
    }

    /**
     * Get the action name from the GET parameter.
     *
     * @return string
     */
    private function getActionFromParam() {
        return isset($_GET['a']) ? strtolower($_GET['a']) : 'index';
    }
}