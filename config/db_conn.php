<?php
require_once __DIR__ . '/env_reader.php';
require_once __DIR__ . '/../vendor/autoload.php';

use RedBeanPHP\R as R;

// --- 1) Configuración común ---
$config = [
    'host'        => env('DB_HOST'),
    'user'        => env('DB_USER'),
    'pass'        => env('DB_PASS'),
    'db'          => env('DB_NAME'),
    'port'        => env('DB_PORT'),
    'charset'     => env('DB_CHARSET', 'utf8mb4'),
    'environment' => env('APP_ENV', 'production'),
];

// Construye una única vez la cadena DSN
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s;port=%s',
    $config['host'],
    $config['db'],
    $config['charset'],
    $config['port']
);

// Opciones PDO reutilizables
$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- 1) PDO Connection ---
try
{
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $pdoOptions);
}
catch (PDOException $e)
{
    die("Error de conexión PDO: " . $e->getMessage());
}
