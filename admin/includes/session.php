<?php
require_once __DIR__ . '/session_config.php';
require_once dirname(__DIR__) . '/../config/db_conn.php';
require_once __DIR__ . '/security_functions.php';
// Importar RedBeanPHP
use RedBeanPHP\R as R;

date_default_timezone_set(env('APP_TIMEZONE'));
$isDebug = env('APP_DEBUG') === 'true';
error_reporting($isDebug ? E_ALL : E_ERROR | E_PARSE | E_CORE_ERROR);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('display_startup_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

// Verificar y renovar la sesión
function checkSession()
{
	global $conn;
	$max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
	$current_time = time();

	// Intentar recuperar la sesión desde la cookie persistente si la sesión actual no existe
	if (!isset($_SESSION['admin']) && isset($_COOKIE['persistent_session']))
	{
		$admin_id = filter_var($_COOKIE['persistent_session'], FILTER_SANITIZE_NUMBER_INT);
		if ($admin_id)
		{
			// Usar RedBeanPHP para verificar el usuario
			$admin = R::load('admin', $admin_id);
			if ($admin->id)
			{
				$_SESSION['admin'] = $admin_id;
				$_SESSION['last_activity'] = $current_time;
				// Guardamos la IP actual pero no la usamos para validaciones
				$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
				$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
				$_SESSION['created'] = $current_time;

				// Renovar la cookie persistente
				setcookie(
					'persistent_session',
					$admin_id,
					[
						'expires' => time() + $max_lifetime,
						'path' => '/',
						'domain' => '',
						'secure' => isset($_SERVER['HTTPS']),
						'httponly' => true,
						'samesite' => 'Lax'
					]
				);
			}
		}
	}

	// CAMBIO: Actualizamos la IP y User-Agent para fines de auditoría, pero no validamos
	if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'])
	{
		// Registrar el cambio de IP para auditoría
		if (isset($_SESSION['admin']))
		{
			logDeviceChange($_SESSION['admin'], 'ip_change', $_SESSION['ip_address'], $_SERVER['REMOTE_ADDR']);
		}
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
	}

	if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'])
	{
		// Registrar el cambio de user-agent para auditoría
		if (isset($_SESSION['admin']))
		{
			logDeviceChange($_SESSION['admin'], 'user_agent_change', $_SESSION['user_agent'], $_SERVER['HTTP_USER_AGENT']);
		}
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	}

	if (
		isset($_SESSION['last_activity']) &&
		($current_time - $_SESSION['last_activity']) > $max_lifetime
	)
	{
		// La sesión ha expirado
		session_unset();
		session_destroy();
		// También eliminar la cookie persistente
		setcookie('persistent_session', '', time() - 3600, '/');
		header('location: ../admin/index.php?error=session_expired');
		exit();
	}

	// Renovar el tiempo de la sesión
	$_SESSION['last_activity'] = $current_time;

	// Regenerar el ID de sesión periódicamente (cada 12 horas en lugar de 30 minutos)
	if (!isset($_SESSION['created']))
	{
		$_SESSION['created'] = time();
	}
	else if (time() - $_SESSION['created'] > 43200) // 12 horas
	{
		// Guardar el ID admin antes de regenerar
		$admin_id = $_SESSION['admin'] ?? null;

		session_regenerate_id(true);

		// Asegurar que el ID admin sigue presente si existía
		if ($admin_id)
		{
			$_SESSION['admin'] = $admin_id;
		}
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

	// Renovar la cookie persistente si existe
	if (isset($_SESSION['admin']) && isset($_COOKIE['persistent_session']))
	{
		setcookie(
			'persistent_session',
			$_SESSION['admin'],
			[
				'expires' => time() + $max_lifetime,
				'path' => '/',
				'domain' => '',
				'secure' => isset($_SERVER['HTTPS']),
				'httponly' => true,
				'samesite' => 'Lax'
			]
		);
	}

	// Actualizar la última actividad en la tabla active_sessions si hay un usuario logueado
	if (isset($_SESSION['admin']) && isset($_SESSION['device_token']))
	{
		try
		{
			// Buscar la sesión activa existente
			$activeSession = R::findOne(
				'active_sessions',
				'user_id = ? AND device_token = ?',
				[$_SESSION['admin'], $_SESSION['device_token']]
			);

			// Si existe, actualizar la última actividad
			if ($activeSession)
			{
				$activeSession->last_activity = R::isoDateTime();
				R::store($activeSession);
			}
		}
		catch (Exception $e)
		{
			// Si hay un error, continuamos sin interrumpir la sesión
		}
	}
}

// Función para registrar cambios de dispositivo para auditoría
function logDeviceChange($user_id, $change_type, $old_value, $new_value)
{
	try
	{
		// Crear un nuevo registro de log usando RedBeanPHP
		$log = R::dispense('session_logs');
		$log->user_id = $user_id;
		$log->log_type = $change_type;
		$log->old_value = $old_value;
		$log->new_value = $new_value;
		$log->created_at = R::isoDateTime();
		R::store($log);
	}
	catch (Exception $e)
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

// Cargar información del usuario con RedBeanPHP
$user = R::load('admin', $_SESSION['admin'])->export();

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

// Actualizar last_login time usando RedBeanPHP
$admin = R::load('admin', $_SESSION['admin']);
$admin->last_login = date('Y-m-d H:i:s');
R::store($admin);

// Obtener datos de la empresa usando RedBeanPHP
$company = R::load('company_data', 1)->export();

$company_name = $company['company_name'];
$company_name_short = $company['company_name_short'];
$app_name = $company['app_name'];
$app_version = $company['app_version'];
$developer_name = $company['developer_name'];

$photoPath = '../images/admins/' . $user['photo'];
$defaultPhoto = '../images/admins/profile.png';
$photoSrc = (!empty($user['photo']) && file_exists($photoPath)) ? $photoPath : $defaultPhoto;
