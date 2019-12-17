<?php
namespace App;

class Config {
    /**
     * Database connection details.
     */
    const DB_SERVER = '127.0.0.1';
    const DB_NAME = 'miniproject';
    const DB_USER = 'michael';
    const DB_PASS = 'P@ssw0rd';

    /**
     * Hosts that are allowed to use write API operations.
     */
    const ALLOWED_HOSTS = array(
        '127.0.0.1',
        'localhost',
        '92.6.90.248', // My IP
        '34.203.109.1' // AWS IP
    );

    /**
     * JSON Web Token (JWT) config.
     */
    const JWT_KEY = 'duAM1RgJQO77LY7AkR8dMQO2JdURQ6ZU';
}