<?php
require_once __DIR__ . '/env_reader.php';
// Incluir RedBeanPHP (asumiendo que se instaló con Composer)
require_once __DIR__ . '/../vendor/autoload.php';

// Utilizar el namespace de RedBeanPHP
use RedBeanPHP\R as R;

// Obtener las variables de entorno
$host = env('DB_HOST');
$user = env('DB_USER');
$pass = env('DB_PASS');
$db   = env('DB_NAME');
$port = env('DB_PORT', '3306'); // Puerto por defecto para MySQL
$charset = env('DB_CHARSET', 'utf8mb4'); // Charset por defecto para MySQL

// Construir el DSN de la conexión
$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";

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

    // Configurar RedBeanPHP para usar la conexión existente
    R::setup($dsn, $user, $pass);

    // Modo de congelación (freeze = false en desarrollo, true en producción)
    // En modo desarrollo, RedBean puede modificar esquemas automáticamente
    $environment = env('APP_ENV', 'development');
    R::freeze($environment === 'production');

    // Configurar opciones adicionales de RedBeanPHP
    R::useFeatureSet('novice/latest');

    // Aquí se puede continuar con el uso de $pdo o R::
}
catch (PDOException $e)
{
    // Registrar el error (idealmente en un sistema de logs)
    error_log("Error de conexión a la base de datos: " . $e->getMessage());

    // Mostrar un mensaje genérico para el usuario sin exponer detalles sensibles
    die("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
}
