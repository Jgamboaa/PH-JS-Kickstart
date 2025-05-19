<?php
require_once __DIR__ . '/session_config.php';
require_once dirname(__DIR__) . '/../config/db_conn.php';
require_once __DIR__ . '/security_functions.php';
date_default_timezone_set(env('APP_TIMEZONE'));


// Verificar y renovar la sesión
function checkSession()
{
	$max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
	$current_time = time();

	if (
		isset($_SESSION['last_activity']) &&
		($current_time - $_SESSION['last_activity']) > $max_lifetime
	)
	{
		// La sesión ha expirado
		session_unset();
		session_destroy();
		header('location: ../index.php');
		exit();
	}

	// Renovar el tiempo de la sesión
	$_SESSION['last_activity'] = $current_time;

	// Regenerar el ID de sesión periódicamente (cada 30 minutos)
	if (!isset($_SESSION['created']))
	{
		$_SESSION['created'] = time();
	}
	else if (time() - $_SESSION['created'] > 1800)
	{
		session_regenerate_id(true);
		$_SESSION['created'] = time();
	}
}

checkSession();

if (!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '')
{
	header('location: index.php');
	exit();
}

$sql = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['admin']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el usuario tiene MFA requerido pero no habilitado
if ($user['tfa_required'] == 1 && $user['tfa_enabled'] == 0)
{
	// Limpiar todas las variables de sesión
	$_SESSION = array();

	// Destruir la cookie de sesión específica de admin
	if (isset($_COOKIE['admin_session']))
	{
		setcookie('admin_session', '', time() - 3600, '/admin', '', true, true);
	}

	// Destruir la sesión
	session_destroy();

	// Redireccionar al login con parámetro específico
	header('location: index.php?setup_2fa=required');
	exit();
}

//update last_login time
$now_login = date('Y-m-d H:i:s');
$sql = "UPDATE admin SET last_login = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$now_login, $_SESSION['admin']]);

$sql = "SELECT * FROM company_data WHERE id = 1 LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$company_name = $data['company_name'];
$company_name_short = $data['company_name_short'];
$app_name = $data['app_name'];
$app_version = $data['app_version'];
$developer_name = $data['developer_name'];

$photoPath = '../images/admins/' . $user['photo'];
$defaultPhoto = '../images/admins/profile.png';
$photoSrc = (!empty($user['photo']) && file_exists($photoPath)) ? $photoPath : $defaultPhoto;

if (env('APP_DEBUG') === 'true')
{
	// Configuración para modo de desarrollo
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	ini_set('log_errors', 1);
}
else
{
	// Configuración para producción
	error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	ini_set('log_errors', 1);
}
