<?php


declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

spl_autoload_register(function ($class) {
    require __DIR__ . '/src/class.php';
});

header("Content-type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: application/json");
header("Access-Control-Allow-Headers: *");

//echo $_SERVER["REQUEST_URI"];
$parts = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
//echo json_encode($parts);

array_shift($parts);

$main_req = $parts[1] ?? null;
$user_id = $parts[3] ?? null;
$user_name = $parts[2] ?? null;
if ($main_req !== null) {
    $responserOf = new Reponser;
    $responserOf->ResProcesser($_SERVER['REQUEST_METHOD'], $main_req, $user_name, $user_id);
}

?>