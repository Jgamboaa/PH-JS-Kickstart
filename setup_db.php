<?php

// Incluir autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Función para determinar si el script se está ejecutando como parte del proceso de instalación
function isRunningFromInstaller()
{
    return defined('RUNNING_FROM_INSTALLER') && RUNNING_FROM_INSTALLER === true;
}

// Si no está definida la constante y no estamos siendo incluidos por otro script
if (!defined('RUNNING_FROM_INSTALLER') && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__))
{
    // Solo mostrar mensajes de bienvenida si se ejecuta directamente
    echo "==========================================================\n";
    echo "Inicializando el sistema - Ejecutando migraciones y semillas\n";
    echo "==========================================================\n\n";
}

// Verificar si Phinx está instalado
if (!file_exists(__DIR__ . '/vendor/bin/phinx') && !file_exists(__DIR__ . '/vendor/bin/phinx.bat'))
{
    $errorMsg = "ERROR: No se encontró Phinx en la carpeta vendor/bin.\n";
    $errorMsg .= "Por favor, ejecuta 'composer install' primero.\n";

    if (isRunningFromInstaller())
    {
        throw new Exception($errorMsg);
    }
    else
    {
        echo $errorMsg;
        exit(1);
    }
}

// Verificar si el archivo .env existe
if (!file_exists(__DIR__ . '/.env'))
{
    $errorMsg = "ERROR: No se encontró el archivo .env.\n";
    $errorMsg .= "Por favor, ejecuta primero el instalador para configurar el sistema.\n";

    if (isRunningFromInstaller())
    {
        throw new Exception($errorMsg);
    }
    else
    {
        echo $errorMsg;
        exit(1);
    }
}

// Ejecutar migraciones
if (!isRunningFromInstaller())
{
    echo "Ejecutando migraciones...\n";
}

$migrationCommand = PHP_OS === 'WINNT' ?
    "php \"" . __DIR__ . "/vendor/bin/phinx\" migrate" :
    "php \"" . __DIR__ . "/vendor/bin/phinx\" migrate";

if (!isRunningFromInstaller())
{
    echo "Comando: $migrationCommand\n";
}

passthru($migrationCommand, $result);

if ($result !== 0)
{
    $errorMsg = "\nERROR al ejecutar las migraciones. Código de error: $result\n";

    if (isRunningFromInstaller())
    {
        throw new Exception($errorMsg);
    }
    else
    {
        echo $errorMsg;
        exit(1);
    }
}

// Ejecutar semillas
if (!isRunningFromInstaller())
{
    echo "\nEjecutando semillas...\n";
}

$seedCommand = PHP_OS === 'WINNT' ?
    "php \"" . __DIR__ . "/vendor/bin/phinx\" seed:run" :
    "php \"" . __DIR__ . "/vendor/bin/phinx\" seed:run";

if (!isRunningFromInstaller())
{
    echo "Comando: $seedCommand\n";
}

passthru($seedCommand, $result);

if ($result !== 0)
{
    $errorMsg = "\nERROR al ejecutar las semillas. Código de error: $result\n";

    if (isRunningFromInstaller())
    {
        throw new Exception($errorMsg);
    }
    else
    {
        echo $errorMsg;
        exit(1);
    }
}

if (!isRunningFromInstaller())
{
    echo "\n==========================================================\n";
    echo "¡Proceso completado!\n";
    echo "El sistema ha sido inicializado correctamente.\n";
    echo "Usuario administrador: admin@admin.com\n";
    echo "Contraseña: admin\n";
    echo "==========================================================\n";
}
