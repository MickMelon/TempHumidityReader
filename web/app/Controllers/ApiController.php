<?php
namespace App\Controllers;

use App\Models\SensorModel;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    /**
     * Creates a new sensor reading entry.
     *
     * @return void
     */
    public function create() {
        if (isset($_GET['temperature']) && isset($_GET['humidity'])) {
            $temperature = $_GET['temperature'];
            $humidity = $_GET['humidity'];

            $this->sensorModel->save($temperature, $humidity);
            echo 'Saved';
            return;
        }

        echo 'Invalid parameters specified';
    }
}