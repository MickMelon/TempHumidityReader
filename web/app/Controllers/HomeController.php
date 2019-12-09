<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\View;

class HomeController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    /**
     * Displays the main page.
     *
     * @return void
     */
    public function index() {
        $view = new View('home');
        $view->render();
    }
}