<?php
namespace App;

use PDO;

class Database {
    private static $instance = NULL;
    /**
     * The constructor and clone functions are set private
     * so that they cannot be called from outside the class.
     */
    private function __construct() {}
    private function __clone() {}

    /**
     * Returns the singleton database instance. If there is none, 
     * it will create it.
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::setInstance();
        }

        return self::$instance;
    }
    
    /**
     * Configure and set the singleton database instance.
     */
    private static function setInstance() {
        $pdo_options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                             PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
                             
        try {
            self::$instance = new PDO(
                'mysql:host=' . Config::DB_SERVER .
                ';dbname=' . Config::DB_NAME,
                Config::DB_USER,
                Config::DB_PASS,
                $pdo_options);
        } catch (Exception $ex) {
            echo $ex->getMessage() . '<br />';
            die('<p style="color: red;">PDO ERROR: Could not connect to database!</p>');
        }
    }
}