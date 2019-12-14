<?php
namespace App\Models;

use App\Database;
use PDO;

class SensorModel {
    /**
     * Get the latest sensor reading.
     *
     * @return json
     */
    public function latest() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings ' .
               "ORDER BY id DESC LIMIT 1";
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetch());
    }

    /**
     * Get all the records within a timeframe.
     *
     * @param string $time Hour, day, week, or month
     * @param int $qty The number of $time, ie 4 hours
     * @return json
     */
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

    /**
     * Get all the sensor readings.
     *
     * @return json
     */
    public function all() {
        $db = Database::getInstance();

        $sql = 'SELECT * FROM sensor_readings';
        $query = $db->prepare($sql);
        $query->execute();

        return json_encode($query->fetchAll());
    }

    /**
     * Save a sensor reading as a new record in the database.
     *
     * @param float $temperature HTU21D temperature.
     * @param float $humidity HTU21D humidity.
     * @param float $systemTemp Raspberry Pi system temperature.
     * 
     * @return void
     */
    public function save($temperature, $humidity, $systemTemp) {
        $db = Database::getInstance();

        $sql = 'INSERT INTO sensor_readings (humidity, temperature, system_temp) ' .
               'VALUES (:humidity, :temperature, :system_temp)';
        $query = $db->prepare($sql);
        $query->bindParam(':humidity', $humidity, PDO::PARAM_STR);
        $query->bindParam(':temperature', $temperature, PDO::PARAM_STR);
        $query->bindParam(':system_temp', $systemTemp, PDO::PARAM_STR);

        $query->execute();
    }
}