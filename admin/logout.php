<?php
require_once 'includes/session_config.php';

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión específica de admin
if (isset($_COOKIE['admin_session'])) {
    setcookie('admin_session', '', time() - 3600, '/admin', '', true, true);
}

// Destruir la sesión
session_destroy();

// Limpiar el buffer de salida
ob_clean();

// Redireccionar al login
header('location: ../index.php');
exit();
