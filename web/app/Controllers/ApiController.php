<?php
namespace App\Controllers;

use App\Models\SensorModel;

class ApiController {
    private $sensorModel;

    public function __construct() {
        $this->sensorModel = new SensorModel();
    }

    public function get() {
        $all = $this->sensorModel->all();

        header("content-type: application/json");
        echo $all;
    }

    /**
     * Creates a new sensor reading entry.
     *
     * @return void
     */
    public function create() {
       /* if (isset($_GET['temperature']) && isset($_GET['humidity'])) {
            $temperature = $_GET['temperature'];
            $humidity = $_GET['humidity'];

            $this->sensorModel->save($temperature, $humidity);
            echo 'Saved';
            return;
        }

        echo 'Invalid parameters specified';*/

        $jsonbody = file_get_contents('php://input');
        $json = json_decode($jsonbody, false);

        echo '********\n';
        echo 'The JSON is: ' . $json->temperature . ' and ' . $json->humidity;

        $this->sensorModel->save($json->temperature, $json->humidity);
    }
}