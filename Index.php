<?php
// require 'src/seed/dbseed.php';
require 'bootstrap.php';

use Src\Controller\TesteController;
use Src\Controller\APIController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = str_replace('/bbts_api','',$_SERVER['QUERY_STRING']);
$gateway = str_replace('/bbts_api/','',$_SERVER['REDIRECT_URL']);
$gateway = str_replace('/','',$gateway);
$exploded = array();
parse_str($uri, $exploded);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$controller = new APIController($dbConnection, $requestMethod, $exploded,$gateway);
$controller->processRequest();