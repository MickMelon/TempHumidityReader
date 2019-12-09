<?php
namespace App\Controllers;

use App\Models\SensorModel;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    public function get() {
        if (isset($_GET['time']) && (strtolower($time) != 'all')) {
            $data = $this->sensorModel->time($_GET['time'], 1);
        } else {
            $data = $this->sensorModel->all();
        }        

        header("content-type: application/json");
        echo $data;
    }

    /**
     * Creates a new sensor reading entry.
     *
     * @return void
     */
    public function create() {
        $jsonbody = file_get_contents('php://input');
        $json = json_decode($jsonbody, false);

        echo 'The JSON is: ' . $json->temperature . ' and ' . $json->humidity;

        $this->sensorModel->save($json->temperature, $json->humidity);
    }
}