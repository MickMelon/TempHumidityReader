<?php
namespace App\Models;

use App\Database;
use PDO;

class SensorModel {
    public function lastHour() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               'WHERE datetime >= DATE_SUB(NOW(), INTERVAL 1 HOUR)';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function lastDay() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               'WHERE datetime >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function lastWeek() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               'WHERE datetime >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    public function lastMonth() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               'WHERE datetime >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
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