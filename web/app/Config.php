<?php
namespace App;

class Config {
    const DB_SERVER = '127.0.0.1';
    const DB_NAME = 'miniproject';
    const DB_USER = 'root';
    const DB_PASS = 'P@ssw0rd';

    const ALLOWED_HOSTS = array(
        '127.0.0.1',
        'localhost',
        '89.238.154.167', // My IP
        '35.174.12.122' // AWS IP
    );
}