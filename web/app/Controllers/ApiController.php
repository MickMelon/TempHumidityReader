<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\Json;
use App\Config;

class ApiController {
    /**
     * The SensorModel
     *
     * @var App\Models\SensorModel
     */
    private $sensorModel;

    /**
     * Creates a new instance of the ApiController class.
     */
    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    /**
     * Shows the latest sensor readings in JSON format.
     *
     * @return void
     */
    public function latest() {
        $latest = $this->sensorModel->latest();

        $json = new Json($latest, true);
        $json->show();
    }

    /**
     * Gets all the sensor readings of all time or within
     * a given timeframe.
     *
     * @return void
     */
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
        if (!$this->validateClient()) {
            http_response_code(403);
            die('You are not allowed to access this resource.');
        }

        $jsonbody = file_get_contents('php://input');
        $json = json_decode($jsonbody, false);

        $this->sensorModel->save($json->temperature, $json->humidity, $json->system_temp);

        $showJson = new Json(['message' => "Received data: [temp:$json->temperature|hum:$json->humidity|systmp:$json->system_temp]"]);
    }

    /**
     * Checks to see if the client is permitted to perform
     * write operations.
     *
     * @return bool
     */
    private function validateClient() {
        return (in_array($_SERVER['REMOTE_ADDR'], Config::ALLOWED_HOSTS));
    }
}