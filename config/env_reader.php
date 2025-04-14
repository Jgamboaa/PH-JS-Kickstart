<?php

/**
 * Función para cargar las variables del archivo .env
 * @param string $path Ruta al archivo .env (opcional)
 * @return array Variables cargadas
 */
function loadEnv($path = null)
{
    // Si no se proporciona ruta, usar la ubicación predeterminada
    if ($path === null)
    {
        $path = __DIR__ . '/../.env';
    }

    // Verificar si el archivo existe
    if (!file_exists($path))
    {
        die("Error: El archivo .env no existe en la ruta: $path");
    }

    // Leer el archivo
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env_vars = [];

    // Procesar cada línea
    foreach ($lines as $line)
    {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0)
        {
            continue;
        }

        // Analizar asignación de variable
        if (strpos($line, '=') !== false)
        {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Establecer la variable en $_ENV y como variable de entorno
            $_ENV[$name] = $value;
            putenv("$name=$value");
            $env_vars[$name] = $value;
        }
    }

    return $env_vars;
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
    $value = getenv($key);

    if ($value === false)
    {
        return $default;
    }

    return $value;
}
