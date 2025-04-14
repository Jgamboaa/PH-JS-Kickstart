<?php
require_once __DIR__ . '/env_reader.php';

$config = array(
    'host' => env('DB_HOST'),
    'user' => env('DB_USER'),
    'pass' => env('DB_PASS'),
    'db'   => env('DB_NAME')
);

$conn = null;
try
{
    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error)
    {
        throw new mysqli_sql_exception($conn->connect_error);
    }
}
catch (mysqli_sql_exception $e)
{
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

$conn->set_charset('utf8');
