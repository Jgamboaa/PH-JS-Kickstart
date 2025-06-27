<?php

// Incluir autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Mensaje de bienvenida
echo "==========================================================\n";
echo "Inicializando el sistema - Ejecutando migraciones y semillas\n";
echo "==========================================================\n\n";

// Ejecutar migraciones
echo "Ejecutando migraciones...\n";
$migrationCommand = 'php ../vendor/bin/phinx migrate -c ../phinx.php';
echo shell_exec($migrationCommand) . "\n";

// Ejecutar semillas
echo "Ejecutando semillas...\n";
$seedCommand = 'php ../vendor/bin/phinx seed:run -c ../phinx.php';
echo shell_exec($seedCommand) . "\n";

echo "==========================================================\n";
echo "¡Proceso completado!\n";
echo "El sistema ha sido inicializado correctamente.\n";
echo "Usuario administrador: admin@admin.com\n";
echo "Contraseña: admin\n";
echo "==========================================================\n";
