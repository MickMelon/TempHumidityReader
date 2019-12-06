<?php
namespace App\Controllers;

use App\Models\SensorModel;

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
}