<?php
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/security_functions.php';

// Usar RedBeanPHP
use RedBeanPHP\R as R;

$mail_support = env('MAIL_SUPPORT');
$isDebug = env('APP_DEBUG') === 'true';
error_reporting($isDebug ? E_ALL : E_ERROR | E_PARSE | E_CORE_ERROR);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('display_startup_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

header('Content-Type: application/json');
$response = ['status' => false, 'message' => '', 'redirect' => false];

// Función para registrar la sesión del dispositivo
function registerDeviceSession($user_id, $device_token, $ip_address, $user_agent)
{
	try
	{
		// Buscar sesión existente por device_token
		$existingSession = R::findOne('activesessions', 'device_token = ?', [$device_token]);

		if ($existingSession)
		{
			// Actualizar sesión existente
			$existingSession->user_id = $user_id;
			$existingSession->ip_address = $ip_address;
			$existingSession->user_agent = $user_agent;
			$existingSession->last_activity = R::isoDateTime();
			R::store($existingSession);
		}
		else
		{
			// Crear nueva sesión
			$session = R::dispense('activesessions');
			$session->user_id = $user_id;
			$session->device_token = $device_token;
			$session->ip_address = $ip_address;
			$session->user_agent = $user_agent;
			$session->last_activity = R::isoDateTime();
			$session->created_at = R::isoDateTime();
			R::store($session);
		}
	}
	catch (Exception $e)
	{
		// Si hay un error al registrar la sesión, continuamos de todos modos
		// pero idealmente deberíamos registrar este error
	}
}

// Verificación de autenticación por contraseña
if (isset($_POST['login_password']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$username = isset($_SESSION['username']) ? $_SESSION['username'] : filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
	$password = $_POST['password'];

	if (checkLoginAttempts($username, $mail_support))
	{
		$admin = R::findOne('admin', 'username = ?', [$username]);

		if (!$admin)
		{
			updateLoginAttempts($username);
			$response['message'] = 'Usuario no encontrado';
		}
		else
		{
			if (password_verify($password, $admin->password))
			{
				if ($admin->admin_estado == 1)
				{
					$response['message'] = 'Tu cuenta ha sido deshabilitada';
				}
				else
				{
					resetLoginAttempts($username);

					// Generamos un nuevo ID de sesión para este dispositivo
					session_regenerate_id(true);

					// Agregamos un token único para este dispositivo
					$device_token = bin2hex(random_bytes(16));
					$_SESSION['device_token'] = $device_token;

					// Registramos la sesión para este dispositivo
					registerDeviceSession($row['id'], $device_token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

					// Almacenamos IP y user agent para seguimiento
					$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
					$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

					logLoginActivity($username, true);

					// NUEVO: Verificar si 2FA es requerido pero no está configurado
					if ($admin->tfa_required == 1 && $admin->tfa_enabled == 0)
					{
						// El usuario debe configurar 2FA
						$_SESSION['setup_2fa_pending'] = true;
						$_SESSION['setup_2fa_user_id'] = $admin->id;
						$_SESSION['setup_2fa_username'] = $username;

						// Responder con JSON para activar la configuración 2FA
						echo json_encode([
							'status' => true,
							'require_2fa_setup' => true,
							'user_id' => $admin->id,
							'username' => $username,
							'message' => 'Es necesario configurar la autenticación de dos factores para continuar'
						]);
						exit();
					}
					// Verificar si el usuario tiene 2FA activado
					else if ($admin->tfa_enabled == 1)
					{
						// Configurar sesión para verificación 2FA
						$_SESSION['2fa_pending'] = true;
						$_SESSION['2fa_user_id'] = $admin->id;

						// Responder con JSON para activar la verificación 2FA en la misma página
						echo json_encode([
							'status' => true,
							'require_2fa' => true,
							'user_id' => $admin->id,
							'message' => 'Se requiere verificación de dos factores'
						]);
						exit();
					}
					else
					{
						// Proceso normal sin 2FA
						// Validación para el saludo según el género y si es primera vez o no
						if (empty($admin->last_login))
						{
							$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
						}
						else
						{
							$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
						}

						$_SESSION['admin'] = $admin->id;
						$_SESSION['last_activity'] = time();

						// Actualizar último login
						$admin->last_login = R::isoDateTime();
						R::store($admin);

						$response['status'] = true;
						$response['message'] = $saludo_login . ' ' . $admin->user_firstname . '!';
						$response['redirect'] = true;
						$response['redirect_url'] = 'home.php';
					}
				}
			}
			else
			{
				updateLoginAttempts($username);
				$response['message'] = 'Contraseña incorrecta';
			}
		}
	}
	else
	{
		$response['message'] = 'Cuenta bloqueada temporalmente. Por favor contacta soporte';
		$response['blocked'] = true;
	}
}
// NUEVO: Procesar solicitud de configuración de 2FA
else if (isset($_POST['setup_2fa_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$user_id = (int)$_POST['user_id'];
	$otp_code = trim($_POST['otp_code']);
	$response = ['status' => false, 'message' => '', 'redirect' => false];

	// Verificar que hay una sesión de configuración pendiente o regenerar si falta
	if (!isset($_SESSION['setup_2fa_pending']))
	{
		// Intentamos regenerar el estado si tenemos el user_id y coincide con alguna información guardada
		if (isset($_SESSION['setup_2fa_user_id']) && $_SESSION['setup_2fa_user_id'] == $user_id)
		{
			$_SESSION['setup_2fa_pending'] = true;
		}
		else
		{
			$response['message'] = 'No hay una configuración 2FA pendiente';
			$response['debug'] = ['error' => 'setup_2fa_pending not set'];
			echo json_encode($response);
			exit();
		}
	}

	if (!isset($_SESSION['setup_2fa_user_id']))
	{
		$_SESSION['setup_2fa_user_id'] = $user_id; // Establecemos el ID si no existe pero confiamos en el formulario
		$response['message'] = 'ID de usuario establecido desde el formulario';
		$response['debug'] = ['warning' => 'setup_2fa_user_id set from form'];
	}

	if ($_SESSION['setup_2fa_user_id'] != $user_id)
	{
		$response['message'] = 'ID de usuario no coincide';
		$response['debug'] = ['session_id' => $_SESSION['setup_2fa_user_id'], 'request_id' => $user_id];
		echo json_encode($response);
		exit();
	}

	// Verificar que el secreto temporal existe
	if (!isset($_SESSION['temp_tfa_secret']))
	{
		// Si no existe, intentamos generarlo nuevamente
		require_once 'includes/functions/2fa_functions.php';
		$_SESSION['temp_tfa_secret'] = generateTOTPSecret();
		$response['message'] = 'Secreto temporal regenerado. Por favor refresque la página e intente nuevamente.';
		$response['debug'] = ['regenerated_secret' => true];
		echo json_encode($response);
		exit();
	}

	// Verificar el código OTP
	require_once 'includes/functions/2fa_functions.php';
	if (verifyOTP($_SESSION['temp_tfa_secret'], $otp_code))
	{
		// Generar códigos de respaldo
		$backupCodes = generateBackupCodes();

		// Guardar el secreto y activar 2FA
		$admin = R::load('admin', $user_id);
		$admin->tfa_secret = $_SESSION['temp_tfa_secret'];
		$admin->tfa_enabled = 1;
		$admin->tfa_backup_codes = json_encode($backupCodes);

		if (R::store($admin))
		{
			// Actualizar último login
			$admin->last_login = R::isoDateTime();
			R::store($admin);

			// Mensaje de bienvenida
			if (empty($admin->last_login))
			{
				$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
			}
			else
			{
				$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
			}

			// IMPORTANTE: Limpiar variables de sesión temporales ANTES de establecer la sesión normal
			$secretToUse = $_SESSION['temp_tfa_secret']; // Guardamos el valor antes de limpiarlo
			unset($_SESSION['temp_tfa_secret']);
			unset($_SESSION['setup_2fa_pending']);
			unset($_SESSION['setup_2fa_user_id']);
			unset($_SESSION['setup_2fa_username']);

			// Establecer sesión normal
			$_SESSION['admin'] = $user_id;
			$_SESSION['last_activity'] = time();

			$response = [
				'status' => true,
				'message' => $saludo_login . ' ' . $admin->user_firstname . '!',
				'redirect' => true,
				'redirect_url' => 'home.php',
				'backup_codes' => $backupCodes
			];
		}
		else
		{
			$response['message'] = 'Error al guardar la configuración 2FA';
		}
	}
	else
	{
		$response['message'] = 'Código de verificación incorrecto. Inténtalo de nuevo.';
	}

	echo json_encode($response);
	exit();
}
// Verificación de autenticación directa por 2FA
else if (isset($_POST['login_2fa']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$username = isset($_SESSION['username']) ? $_SESSION['username'] : filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
	$code = $_POST['code'];
	$user_id = (int)$_POST['user_id'];
	$useBackupCode = isset($_POST['backup_mode']) && $_POST['backup_mode'] == 1;

	// Verificar que el usuario existe
	$admin = R::findOne('admin', 'username = ? AND id = ?', [$username, $user_id]);

	if (!$admin)
	{
		$response['message'] = 'Usuario no encontrado';
		echo json_encode($response);
		exit();
	}

	if ($admin->admin_estado == 1)
	{
		$response['message'] = 'Tu cuenta ha sido deshabilitada';
		echo json_encode($response);
		exit();
	}

	// Verificar el código 2FA
	require_once 'includes/functions/2fa_functions.php';
	$verification_success = false;

	if ($useBackupCode)
	{
		// Verificar código de respaldo
		$verification_success = verifyBackupCode($user_id, $code);
		if (!$verification_success)
		{
			$response['message'] = 'Código de respaldo inválido';
			echo json_encode($response);
			exit();
		}
	}
	else
	{
		// Verificar código TOTP
		$verification_success = verifyOTP($admin->tfa_secret, $code);
		if (!$verification_success)
		{
			$response['message'] = 'Código de verificación inválido';
			echo json_encode($response);
			exit();
		}
	}

	// Si la verificación es exitosa
	if ($verification_success)
	{
		resetLoginAttempts($username);

		// Generamos un nuevo ID de sesión para este dispositivo
		session_regenerate_id(true);

		// Agregamos un token único para este dispositivo
		$device_token = bin2hex(random_bytes(16));
		$_SESSION['device_token'] = $device_token;

		// Registramos la sesión para este dispositivo
		registerDeviceSession($admin->id, $device_token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

		// Almacenamos IP y user agent para seguimiento
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		logLoginActivity($username, true);

		// Actualizar último login
		$admin->last_login = R::isoDateTime();
		R::store($admin);

		// Validación para el saludo según el género y si es primera vez o no
		if (empty($admin->last_login))
		{
			$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
		}
		else
		{
			$saludo_login = ($admin->admin_gender == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
		}

		$_SESSION['admin'] = $admin->id;
		$_SESSION['last_activity'] = time();

		$response['status'] = true;
		$response['message'] = $saludo_login . ' ' . $admin->user_firstname . '!';
		$response['redirect'] = true;
		$response['redirect_url'] = 'home.php';
	}
}
else
{
	$response['message'] = 'Acceso inválido';
}

echo json_encode($response);
exit();
