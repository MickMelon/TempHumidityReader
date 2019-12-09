<?php

session_start();

require_once('vendor/autoload.php');

use App\Config;
use App\Router;

error_reporting(E_ALL);
ini_set("display_errors", 1);

$router = new Router();
$router->start();

/*
use \Firebase\JWT\JWT;

$key = "example_key";
$payload = array(
    "iss" => "192.168.1.6",
    "aud" => "192.168.1.3",
)*/