<?php
namespace App;

use App\Controllers;

class Router {
    /**
     * Gets the specified controller and action and acts on it to 
     * effectively start the app.
     */
    public function start() {
        // Set the controller and action depending on the parameters
        // that are set.
        // e.g. index.php?controller=articles&action=index
        // would set $controller to articles and $action to index
        $controller = isset($_GET['c']) ? strtolower($_GET['c']) : 'home';
        $action = isset($_GET['a']) ? strtolower($_GET['a']) : 'index';

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
}