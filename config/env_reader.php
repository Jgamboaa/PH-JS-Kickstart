<?php

use Dotenv\Dotenv;

// Incluir autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Función para cargar las variables del archivo .env usando vlucas/phpdotenv
 * @param string $path Ruta al directorio que contiene .env (opcional)
 * @return array Variables cargadas
 */
function loadEnv($path = null)
{
    // Si no se proporciona ruta, usar la ubicación predeterminada
    if ($path === null)
    {
        $path = __DIR__ . '/..';
    }

    // Verificar si el archivo .env existe
    $envFile = $path . '/.env';
    if (!file_exists($envFile))
    {
        die("Error: El archivo .env no existe en la ruta: $envFile");
    }

    try
    {
        // Crear instancia de Dotenv
        $dotenv = Dotenv::createImmutable($path);

        // Cargar las variables
        $dotenv->load();

        // Retornar las variables cargadas desde $_ENV
        return $_ENV;
    }
    catch (Exception $e)
    {
        die("Error al cargar el archivo .env: " . $e->getMessage());
    }
}

// Cargar automáticamente las variables al incluir este archivo
loadEnv();

/**
 * Función para obtener una variable de entorno
 * @param string $key Nombre de la variable
 * @param mixed $default Valor predeterminado si no existe la variable
 * @return mixed Valor de la variable o el valor predeterminado
 */
function env($key, $default = null)
{
    // Primero intentar obtener de $_ENV
    if (isset($_ENV[$key]))
    {
        return $_ENV[$key];
    }

    // Como respaldo, intentar getenv()
    $value = getenv($key);

    if ($value === false)
    {
        return $default;
    }

    return $value;
}
