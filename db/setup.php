<?php

// Incluir autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Mensaje de bienvenida
echo "==========================================================\n";
echo "Inicializando el sistema - Ejecutando migraciones y semillas\n";
echo "==========================================================\n\n";

// Ejecutar migraciones
echo "Ejecutando migraciones...\n";
$rootPath = dirname(__DIR__);
$migrationCommand = "php \"{$rootPath}/vendor/bin/phinx\" migrate -c \"{$rootPath}/phinx.php\"";
echo "Comando: $migrationCommand\n";
echo shell_exec($migrationCommand) . "\n";

// Ejecutar semillas
echo "Ejecutando semillas...\n";
$seedCommand = "php \"{$rootPath}/vendor/bin/phinx\" seed:run -c \"{$rootPath}/phinx.php\"";
echo "Comando: $seedCommand\n";
echo shell_exec($seedCommand) . "\n";

echo "==========================================================\n";
echo "¡Proceso completado!\n";
echo "El sistema ha sido inicializado correctamente.\n";
echo "Usuario administrador: admin@admin.com\n";
echo "Contraseña: admin\n";
echo "==========================================================\n";

echo "\nSi hubo problemas con la ejecución, intenta estos comandos manualmente desde la carpeta raíz del proyecto:\n";
echo "php vendor/bin/phinx migrate\n";
echo "php vendor/bin/phinx seed:run\n";
echo "==========================================================\n";
