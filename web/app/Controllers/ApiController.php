<?php
namespace App\Controllers;

use App\Models\SensorModel;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    public function get() {
        if (isset($_GET['time'])) {
            $time = strtolower($_GET['time']);

            if ($time == 'month') {
                $data = $this->sensorModel->lastMonth();
            } elseif ($time == 'week') {
                $data = $this->sensorModel->lastWeek();
            } elseif ($time == 'day') {
                $data = $this->sensorModel->lastDay();
            } elseif ($time == 'hour') {
                $data = $this->sensorModel->lastHour();
            } else {
                $data = $this->sensorModel->all();
            }
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