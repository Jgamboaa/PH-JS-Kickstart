<?php
if (session_status() === PHP_SESSION_NONE)
{
    session_name('admin_session');

    // Establecer y validar directorios para sesiones
    $baseSessionPath = __DIR__ . '/sessions';
    $sessionPath = $baseSessionPath . '/admin';

    // Crear directorio sessions si no existe
    if (!is_dir($baseSessionPath))
    {
        mkdir($baseSessionPath, 0777, true);
    }
    // Crear directorio admin si no existe
    if (!is_dir($sessionPath))
    {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);

    // Configuración de la sesión
    ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);

    // Detectar si estamos en un entorno de desarrollo
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_dev_environment =
        strpos($host, 'localhost') !== false ||
        strpos($host, '.test') !== false ||
        strpos($host, '.dev') !== false ||
        $host === '127.0.0.1';

    // Configuración de cookies específica para admin
    session_set_cookie_params([
        'lifetime' => 30 * 24 * 60 * 60,
        'path' => '/admin', // Restringir a la ruta /admin
        'domain' => '',
        'secure' => !$is_dev_environment, // Desactivar secure en entornos de desarrollo
        'httponly' => true,
        'samesite' => $is_dev_environment ? 'None' : 'Lax' // Más permisivo en desarrollo
    ]);

    session_start();
}
