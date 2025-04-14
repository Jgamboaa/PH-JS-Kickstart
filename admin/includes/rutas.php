<?php
// Importar todas las rutas
$rutas = array_merge(
    require 'routes/home.php',
    require 'routes/system.php',
);

// Obtenemos la ruta desde el POST
$ruta = $_POST['ruta'] ?? '';

// Verificamos si la ruta existe en nuestro arreglo
if (isset($rutas[$ruta]))
{
    echo json_encode($rutas[$ruta]);
}
else
{
    // Devolver 404 en caso de no encontrarse
    echo json_encode(['vista' => 'views/system/404.php', 'scripts' => []]);
}
