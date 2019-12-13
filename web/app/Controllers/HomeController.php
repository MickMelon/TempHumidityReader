<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\View;
use App\Request;

class HomeController {
    /**
     * Displays the main page.
     *
     * @return void
     */
    public function index(Request $request) {
        $view = new View('home');
        $view->show();
    }
}