<?php
require_once __DIR__ . '/env_reader.php';

// Obtener las variables de entorno
$host = env('DB_HOST');
$user = env('DB_USER');
$pass = env('DB_PASS');
$db   = env('DB_NAME');

// Construir el DSN de la conexión
$dsn = "mysql:host=$host;dbname=$db;charset=utf8";

// Opciones para la conexión PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Manejo de errores mediante excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retornar arrays asociativos por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Usar declaraciones preparadas nativas
];

try
{
    // Crear la instancia de PDO
    $conn = new PDO($dsn, $user, $pass, $options);
    // Aquí se puede continuar con el uso de $pdo
}
catch (PDOException $e)
{
    // Registrar el error (idealmente en un sistema de logs)
    error_log("Error de conexión a la base de datos: " . $e->getMessage());

    // Mostrar un mensaje genérico para el usuario sin exponer detalles sensibles
    die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
}
