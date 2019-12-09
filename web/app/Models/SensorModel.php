<?php
namespace App\Models;

use App\Database;
use PDO;

class SensorModel {
    public function time($time, $qty) {
        if (!in_array($time, ['hour', 'day', 'week', 'month'])) {
            return json_encode('time param is invalid');
        } elseif ($qty < 0 || $qty > 100) {
            return json_encode('qty param is invalid');
        }

        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               "WHERE datetime >= DATE_SUB(NOW(), INTERVAL $qty $time)";
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function all() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function save($temperature, $humidity) {
        $db = Database::getInstance();

        $sql = 'INSERT INTO sensor_readings (humidity, temperature) ' .
               'VALUES (:humidity, :temperature)';
        $query = $db->prepare($sql);
        $query->bindParam(':humidity', $humidity, PDO::PARAM_STR);
        $query->bindParam(':temperature', $temperature, PDO::PARAM_STR);

        $query->execute();
    }
}