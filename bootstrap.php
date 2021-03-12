<?php
// require 'vendor/autoload.php';
// use Dotenv\Dotenv;

// $dotenv = new DotEnv(__DIR__);
// $dotenv->load();

// // test code, should output:
// // api://default
// // when you run $ php bootstrap.php
// echo getenv('OKTAAUDIENCE');

require 'vendor/autoload.php';
use Dotenv\Dotenv;

use Src\Connection\MysqlConnection;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

$dbConnection = (new MysqlConnection())->getConnection();