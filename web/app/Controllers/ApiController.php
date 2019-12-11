<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\Json;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    public function latest() {
        $latest = $this->sensorModel->latest();

        $json = new Json($latest, true);
        $json->show();
    }

    public function get() {
        if (isset($_GET['time']) && (strtolower($_GET['time']) != 'all')) {
            $data = $this->sensorModel->time($_GET['time'], 1);
        } else {
            $data = $this->sensorModel->all();
        }        

        $json = new Json($data, true);
        $json->show();
    }

    /**
     * Creates a new sensor reading entry.
     *
     * @return void
     */
    public function create() {
        $jsonbody = file_get_contents('php://input');
        $json = json_decode($jsonbody, false);

        $this->sensorModel->save($json->temperature, $json->humidity, $json->system_temp);

        $showJson = new Json(['message' => "Received data: [temp:$json->temperature|hum:$json->humidity|systmp:$json->system_temp]"]);
    }
}