<?php
require_once __DIR__ . '/session_config.php';
require_once dirname(__DIR__) . '/../config/db_conn.php';
require_once __DIR__ . '/security_functions.php';
date_default_timezone_set(env('APP_TIMEZONE'));

$isDebug = env('APP_DEBUG') === 'true';
$enviroment = env('APP_ENV');
error_reporting($isDebug ? E_ALL : E_ERROR | E_PARSE | E_CORE_ERROR);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('display_startup_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

// Verificar y renovar la sesión
function checkSession()
{
	$max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
	$current_time = time();

	// Intentar recuperar la sesión desde la cookie persistente si la sesión actual no existe
	if (!isset($_SESSION['admin']) && isset($_COOKIE['persistent_session']))
	{
		$admin_id = filter_var($_COOKIE['persistent_session'], FILTER_SANITIZE_NUMBER_INT);
		if ($admin_id)
		{
			global $pdo;
			$stmt = $pdo->prepare("SELECT id FROM admin WHERE id = ? AND admin_estado = 0");
			$stmt->execute([$admin_id]);
			if ($stmt->rowCount() > 0)
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

	// CAMBIO: Eliminamos completamente la verificación de cambio de IP
	// Simplemente actualizamos la IP actual para tenerla registrada por motivos de auditoría
	$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];

	// Ya no verificamos cambios en User-Agent, ya que puede cambiar legítimamente después de actualizaciones

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
	// para reducir la frecuencia de regeneración y minimizar problemas
	if (!isset($_SESSION['created']))
	{
		$_SESSION['created'] = time();
	}
	else if (time() - $_SESSION['created'] > 43200) // 12 horas
	{
		// Guardar el ID admin antes de regenerar
		$admin_id = $_SESSION['admin'];

		session_regenerate_id(true);

		// Asegurar que el ID admin sigue presente
		$_SESSION['admin'] = $admin_id;
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
}

// Ejecutamos la verificación de sesión
checkSession();

if (!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '')
{
	header('location: index.php');
	exit();
}

// Obtener datos del administrador usando PDO
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = :admin_id");
$stmt->execute(['admin_id' => $_SESSION['admin']]);
$user = $stmt->fetch();
$admin_id = $user['id'];

// NUEVO: Verificar si el usuario tiene MFA requerido pero no habilitado
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

// Actualizar last_login time usando PDO
$now_login = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("UPDATE admin SET last_login = :last_login WHERE id = :admin_id");
$stmt->execute([
	'last_login' => $now_login,
	'admin_id' => $_SESSION['admin']
]);

// Obtener datos de la empresa usando PDO
$stmt = $pdo->prepare("SELECT * FROM company_data WHERE id = 1 LIMIT 1");
$stmt->execute();
$data = $stmt->fetch();

$company_name = $data['company_name'];
$company_name_short = $data['company_name_short'];
$app_name = $data['app_name'];
$app_version = $data['app_version'];
$developer_name = $data['developer_name'];

$photoPath = '../images/admins/' . $user['photo'];
$defaultPhoto = '../images/admins/profile.png';
$photoSrc = (!empty($user['photo']) && file_exists($photoPath)) ? $photoPath : $defaultPhoto;
