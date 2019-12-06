<?php
namespace App\Models;

use App\Database;
use PDO;

class SensorModel {
    public function single($id) {

    }

    public function all() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function save() {

    }
}