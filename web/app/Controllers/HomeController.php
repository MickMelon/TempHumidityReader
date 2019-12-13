<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\View;

class HomeController {
    /**
     * Displays the main page.
     *
     * @return void
     */
    public function index() {
        $view = new View('home');
        $view->show();
    }
}