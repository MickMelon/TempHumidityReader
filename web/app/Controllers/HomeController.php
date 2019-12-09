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
        $all = $this->sensorModel->all();

        header("content-type: application/json");
        echo $all;
    }

    public function test() {
        $view = new View('home');
        $view->render();
    }
}