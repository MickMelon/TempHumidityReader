<?php
namespace App\Controllers;

use App\Models\SensorModel;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    public function latest() {
        $latest = $this->sensorModel->latest();

        header("content-type: application/json");
        echo $latest;
    }

    public function get() {
        if (isset($_GET['time']) && (strtolower($_GET['time']) != 'all')) {
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

        echo "Received data: [temp:$json->temperature|hum:$json->humidity|systmp:$json->system_temp]";

        $this->sensorModel->save($json->temperature, $json->humidity, $json->system_temp);
    }
}