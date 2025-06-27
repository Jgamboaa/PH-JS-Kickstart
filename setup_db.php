<?php

// Incluir autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Mensaje de bienvenida
echo "==========================================================\n";
echo "Inicializando el sistema - Ejecutando migraciones y semillas\n";
echo "==========================================================\n\n";

// Verificar si Phinx está instalado
if (!file_exists(__DIR__ . '/vendor/bin/phinx') && !file_exists(__DIR__ . '/vendor/bin/phinx.bat'))
{
    echo "ERROR: No se encontró Phinx en la carpeta vendor/bin.\n";
    echo "Por favor, ejecuta 'composer install' primero.\n";
    exit(1);
}

// Ejecutar migraciones
echo "Ejecutando migraciones...\n";
$migrationCommand = PHP_OS === 'WINNT' ?
    "php \"" . __DIR__ . "/vendor/bin/phinx\" migrate" :
    "php \"" . __DIR__ . "/vendor/bin/phinx\" migrate";
echo "Comando: $migrationCommand\n";
passthru($migrationCommand, $result);

if ($result !== 0)
{
    echo "\nERROR al ejecutar las migraciones. Código de error: $result\n";
    exit(1);
}

// Ejecutar semillas
echo "\nEjecutando semillas...\n";
$seedCommand = PHP_OS === 'WINNT' ?
    "php \"" . __DIR__ . "/vendor/bin/phinx\" seed:run" :
    "php \"" . __DIR__ . "/vendor/bin/phinx\" seed:run";
echo "Comando: $seedCommand\n";
passthru($seedCommand, $result);

if ($result !== 0)
{
    echo "\nERROR al ejecutar las semillas. Código de error: $result\n";
    exit(1);
}

echo "\n==========================================================\n";
echo "¡Proceso completado!\n";
echo "El sistema ha sido inicializado correctamente.\n";
echo "Usuario administrador: admin@admin.com\n";
echo "Contraseña: admin\n";
echo "==========================================================\n";
