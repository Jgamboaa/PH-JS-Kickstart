<?php

/**
 * CLI Setup - Core Installer
 * 
 * Script de instalación por línea de comandos para el Core System
 * Uso: php installer/cli-setup.php
 */

// Verificar que se esté ejecutando desde CLI
if (php_sapi_name() !== 'cli')
{
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

// Definir constantes
define('CLI_MODE', true);
define('BASE_PATH', dirname(__DIR__));

// Incluir la clase Setup
require_once __DIR__ . '/setup.php';

// Función para mostrar texto con colores
function colorText($text, $type = 'info')
{
    $colors = [
        'info'    => "\033[0;36m", // Cian
        'success' => "\033[0;32m", // Verde
        'error'   => "\033[0;31m", // Rojo
        'warning' => "\033[0;33m", // Amarillo
        'reset'   => "\033[0m"     // Reset
    ];

    return $colors[$type] . $text . $colors['reset'];
}

// Función para mostrar el banner
function showBanner()
{
    echo "\n";
    echo colorText("================================================\n", 'success');
    echo colorText("           CORE SYSTEM - INSTALADOR CLI          \n", 'success');
    echo colorText("================================================\n", 'success');
    echo "\n";
}

// Función para solicitar entrada al usuario
function prompt($question, $default = null)
{
    $defaultText = $default ? " [$default]" : "";
    echo colorText("$question$defaultText: ", 'info');
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    return $line ?: $default;
}

// Función para solicitar contraseña (sin mostrarla)
function promptPassword($question)
{
    echo colorText("$question: ", 'info');

    // Intentar ocultar la entrada (solo funciona en sistemas Linux/Unix)
    if (DIRECTORY_SEPARATOR === '/')
    {
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n"; // Nueva línea después de la entrada oculta
    }
    else
    {
        // Para Windows, no podemos ocultar la entrada fácilmente
        $handle = fopen("php://stdin", "r");
        $password = trim(fgets($handle));
        fclose($handle);
    }

    return $password;
}

// Mostrar el banner
showBanner();

// Verificar si ya está instalado
$setup = new Setup();
if ($setup->installed)
{
    echo colorText("\n✘ " . $setup->message . "\n\n", 'warning');
    echo "Si deseas reinstalar, elimina el archivo .env y vuelve a ejecutar este script.\n";
    exit(1);
}

// Verificar si se pueden ejecutar comandos del sistema para instalar dependencias
$canInstallComposer = $setup->canExecuteSystemCommands();
if (!$canInstallComposer)
{
    echo colorText("\n⚠️ Aviso: No se pueden ejecutar comandos del sistema para instalar dependencias con Composer.\n", 'warning');
    echo "Después de la instalación, deberás ejecutar 'composer install' manualmente.\n\n";
}

// Iniciar el proceso de instalación
echo colorText("\n=== Configuración de la Base de Datos ===\n", 'info');
$dbHost = prompt("Servidor de la base de datos", "localhost");
$dbUser = prompt("Usuario de la base de datos", "root");
$dbPass = promptPassword("Contraseña de la base de datos");
$dbName = prompt("Nombre de la base de datos", "core");

echo colorText("\n=== Configuración del Correo Electrónico ===\n", 'info');
$mailHost = prompt("Servidor SMTP", "");
$mailUsername = prompt("Correo electrónico", "");
$mailName = prompt("Nombre del remitente", "Sistema Core");
$mailPassword = promptPassword("Contraseña del correo");
$mailPort = prompt("Puerto SMTP", "587");
$mailEncryption = prompt("Método de encriptación (tls/smtp)", "tls");
$mailSupport = prompt("Correo de soporte", "");

echo colorText("\n=== Configuración del Usuario Administrador ===\n", 'info');
$adminEmail = prompt("Correo electrónico del administrador", "admin@admin.com");
$adminFirstname = prompt("Nombre del administrador", "Usuario");
$adminLastname = prompt("Apellido del administrador", "Administrador");
$adminPassword = promptPassword("Contraseña del administrador");
$adminGender = prompt("Género (0: Masculino, 1: Femenino, 2: Otro)", "0");

// Confirmar los datos ingresados
echo colorText("\n=== Resumen de la Configuración ===\n", 'info');
echo "Base de datos:        $dbHost / $dbName\n";
echo "Usuario DB:           $dbUser\n";
echo "Servidor SMTP:        $mailHost\n";
echo "Correo:               $mailUsername\n";
echo "Admin:                $adminFirstname $adminLastname ($adminEmail)\n";

if ($canInstallComposer)
{
    echo "\nSe intentará instalar automáticamente las dependencias con Composer.\n";
}

$confirm = prompt("\n¿Deseas proceder con la instalación? (s/n)", "s");
if (strtolower($confirm) !== 's')
{
    echo colorText("\nInstalación cancelada por el usuario.\n", 'warning');
    exit(0);
}

// Pasar los datos directamente al objeto Setup en lugar de usar $_POST
echo colorText("\nInstalando el sistema...\n", 'info');

// Configuramos los valores directamente en el objeto Setup
$setup->setDbConfig($dbHost, $dbUser, $dbPass, $dbName);
$setup->setMailConfig($mailHost, $mailUsername, $mailName, $mailPassword, $mailPort, $mailEncryption, $mailSupport);
$setup->setAdminConfig($adminEmail, $adminPassword, $adminFirstname, $adminLastname, $adminGender);

// Procesamos la instalación
$setup->runInstallation();

// Mostrar resultados
if ($setup->error)
{
    echo colorText("\n✘ Error: " . $setup->error . "\n\n", 'error');
    exit(1);
}
else
{
    echo colorText("\n✓ " . $setup->message . "\n\n", 'success');

    if (!$setup->composerInstalled && $canInstallComposer)
    {
        echo colorText("\n⚠️ Aviso: No se pudieron instalar las dependencias de Composer automáticamente.\n", 'warning');
        echo "Por favor, ejecuta manualmente 'composer install' para completar la instalación.\n\n";
    }

    echo colorText("Instalación completada con éxito.\n", 'success');
    echo "Puedes acceder al sistema con las siguientes credenciales:\n";
    echo "Usuario: $adminEmail\n";
    echo "Contraseña: [La contraseña que configuraste]\n\n";
    exit(0);
}
