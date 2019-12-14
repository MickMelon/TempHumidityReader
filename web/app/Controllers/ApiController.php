<?php
namespace App\Controllers;

use App\Models\SensorModel;
use App\Json;
use App\Config;
use App\Request;

use \Firebase\JWT\JWT;

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
    public function latest(Request $request) {
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
    public function get(Request $request) {
        $params = $request->getParams();
        
        if (isset($params['time']) && (strtolower($params['time']) != 'all')) {
            $data = $this->sensorModel->time($params['time'], 1);
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
    public function create(Request $request) {
       /*if (!$this->validateClient()) {
            http_response_code(403);
            die('You are not allowed to access this resource.');
        }*/

        $json = json_decode(file_get_contents('php://input'). true);

        if (!isset($json->jwt)) {
            http_response_code(403);
            die('You are not allowed to access this resource. You did not include JWT in the request.');
        }

        $jwt = JWT::decode($data->jwt, Config::JWT_KEY, array('HS256'));

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