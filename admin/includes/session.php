<?php
require_once __DIR__ . '/session_config.php';
require_once dirname(__DIR__) . '/../config/db_conn.php';
require_once __DIR__ . '/security_functions.php';
date_default_timezone_set(env('APP_TIMEZONE'));

// Verificar y renovar la sesión
function checkSession()
{
	global $conn;
	$max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
	$current_time = time();

	// Verificar posible secuestro de sesión - Modificado para permitir múltiples dispositivos
	if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'])
	{
		// Ya no cerramos la sesión, solo actualizamos el valor
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

		// Opcional: Registrar el cambio de IP para auditoría
		if (isset($_SESSION['admin']))
		{
			logDeviceChange($_SESSION['admin'], 'ip_change', $_SESSION['ip_address'], $_SERVER['REMOTE_ADDR']);
		}
	}

	if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'])
	{
		// Ya no cerramos la sesión, solo actualizamos el valor
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		// Opcional: Registrar el cambio de user-agent para auditoría
		if (isset($_SESSION['admin']))
		{
			logDeviceChange($_SESSION['admin'], 'user_agent_change', $_SESSION['user_agent'], $_SERVER['HTTP_USER_AGENT']);
		}
	}

	if (
		isset($_SESSION['last_activity']) &&
		($current_time - $_SESSION['last_activity']) > $max_lifetime
	)
	{
		// La sesión ha expirado
		session_unset();
		session_destroy();
		header('location: ../admin/index.php?error=session_expired');
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
		// Actualizar tiempo de regeneración
		$_SESSION['created'] = time();
	}

	// Asegurar que las variables de control de sesión estén siempre presentes
	if (!isset($_SESSION['ip_address']))
	{
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
	}

	if (!isset($_SESSION['user_agent']))
	{
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	}

	// Añadir un identificador de dispositivo único si no existe
	if (!isset($_SESSION['device_token']))
	{
		$_SESSION['device_token'] = bin2hex(random_bytes(16));
	}

	// Actualizar la última actividad en la tabla active_sessions si hay un usuario logueado
	if (isset($_SESSION['admin']) && isset($_SESSION['device_token']))
	{
		try
		{
			$sql = "UPDATE active_sessions SET last_activity = NOW() 
					WHERE user_id = ? AND device_token = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute([$_SESSION['admin'], $_SESSION['device_token']]);
		}
		catch (PDOException $e)
		{
			// Si hay un error, continuamos sin interrumpir la sesión
		}
	}
}

// Función para registrar cambios de dispositivo para auditoría
function logDeviceChange($user_id, $change_type, $old_value, $new_value)
{
	global $conn;
	try
	{
		$sql = "INSERT INTO session_logs (user_id, log_type, old_value, new_value, created_at) 
				VALUES (?, ?, ?, ?, NOW())";
		$stmt = $conn->prepare($sql);
		$stmt->execute([$user_id, $change_type, $old_value, $new_value]);
	}
	catch (PDOException $e)
	{
		// Si la tabla no existe, no fallamos, solo ignoramos el log
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

// update last_login time
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

$isDebug = env('APP_DEBUG') === 'true';
error_reporting($isDebug ? E_ALL : E_ERROR | E_PARSE | E_CORE_ERROR);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('display_startup_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);
