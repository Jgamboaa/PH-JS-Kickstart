<?php
require_once __DIR__ . '/env_reader.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$config = array(
    'host' => env('DB_HOST'),
    'user' => env('DB_USER'),
    'pass' => env('DB_PASS'),
    'db'   => env('DB_NAME')
);

try
{
    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    $conn->set_charset('utf8');
}
catch (mysqli_sql_exception $e)
{
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, inténtelo de nuevo más tarde.");
}
