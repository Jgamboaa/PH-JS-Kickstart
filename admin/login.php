<?php
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/security_functions.php';
$mail_support = env('MAIL_SUPPORT');
header('Content-Type: application/json');
$response = ['status' => false, 'message' => '', 'redirect' => false];

// Función para registrar la sesión del dispositivo
function registerDeviceSession($user_id, $device_token, $ip_address, $user_agent)
{
	global $conn;

	try
	{
		// Registramos o actualizamos la sesión para este dispositivo
		$sql = "INSERT INTO active_sessions (user_id, device_token, ip_address, user_agent, last_activity, created_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                ip_address = VALUES(ip_address), 
                user_agent = VALUES(user_agent), 
                last_activity = NOW()";
		$stmt = $conn->prepare($sql);
		$stmt->execute([$user_id, $device_token, $ip_address, $user_agent]);
	}
	catch (PDOException $e)
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
		$sql = "SELECT * FROM admin WHERE username = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute([$username]);

		if ($stmt->rowCount() < 1)
		{
			updateLoginAttempts($username);
			$response['message'] = 'Usuario no encontrado';
		}
		else
		{
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if (password_verify($password, $row['password']))
			{
				if ($row['admin_estado'] == 1)
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
					if ($row['tfa_required'] == 1 && $row['tfa_enabled'] == 0)
					{
						// El usuario debe configurar 2FA
						$_SESSION['setup_2fa_pending'] = true;
						$_SESSION['setup_2fa_user_id'] = $row['id'];
						$_SESSION['setup_2fa_username'] = $username;

						// Responder con JSON para activar la configuración 2FA
						echo json_encode([
							'status' => true,
							'require_2fa_setup' => true,
							'user_id' => $row['id'],
							'username' => $username,
							'message' => 'Es necesario configurar la autenticación de dos factores para continuar'
						]);
						exit();
					}
					// Verificar si el usuario tiene 2FA activado
					else if ($row['tfa_enabled'] == 1)
					{
						// Configurar sesión para verificación 2FA
						$_SESSION['2fa_pending'] = true;
						$_SESSION['2fa_user_id'] = $row['id'];

						// Responder con JSON para activar la verificación 2FA en la misma página
						echo json_encode([
							'status' => true,
							'require_2fa' => true,
							'user_id' => $row['id'],
							'message' => 'Se requiere verificación de dos factores'
						]);
						exit();
					}
					else
					{
						// Proceso normal sin 2FA
						// Validación para el saludo según el género y si es primera vez o no
						if (empty($row['last_login']))
						{
							$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
						}
						else
						{
							$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
						}

						$_SESSION['admin'] = $row['id'];
						$_SESSION['last_activity'] = time();

						// Actualizar último login
						$sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
						$stmt = $conn->prepare($sql);
						$stmt->execute([$row['id']]);

						$response['status'] = true;
						$response['message'] = $saludo_login . ' ' . $row['user_firstname'] . '!';
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
		$sql = "UPDATE admin SET tfa_secret = ?, tfa_enabled = 1, tfa_backup_codes = ? WHERE id = ?";
		$stmt = $conn->prepare($sql);

		if ($stmt->execute([$_SESSION['temp_tfa_secret'], json_encode($backupCodes), $user_id]))
		{
			// Obtener información del usuario para el mensaje de saludo
			$sql = "SELECT user_firstname, admin_gender, last_login FROM admin WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute([$user_id]);
			$row = $stmt->fetch();

			// Actualizar último login
			$sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
			$stmt = $conn->prepare($sql);
			$stmt->execute([$user_id]);

			// Mensaje de bienvenida
			if (empty($row['last_login']))
			{
				$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
			}
			else
			{
				$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
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
				'message' => $saludo_login . ' ' . $row['user_firstname'] . '!',
				'redirect' => true,
				'redirect_url' => 'home.php',
				'backup_codes' => $backupCodes
			];
		}
		else
		{
			$response['message'] = 'Error al guardar la configuración 2FA';
			$response['debug'] = ['sql_error' => $stmt->errorInfo()];
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
	$sql = "SELECT * FROM admin WHERE username = ? AND id = ?";
	$stmt = $conn->prepare($sql);
	$stmt->execute([$username, $user_id]);

	if ($stmt->rowCount() < 1)
	{
		$response['message'] = 'Usuario no encontrado';
		echo json_encode($response);
		exit();
	}

	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row['admin_estado'] == 1)
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
		$verification_success = verifyOTP($row['tfa_secret'], $code);
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
		registerDeviceSession($row['id'], $device_token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

		// Almacenamos IP y user agent para seguimiento
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

		logLoginActivity($username, true);

		// Actualizar último login
		$sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute([$user_id]);

		// Validación para el saludo según el género y si es primera vez o no
		if (empty($row['last_login']))
		{
			$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
		}
		else
		{
			$saludo_login = ($row['admin_gender'] == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
		}

		$_SESSION['admin'] = $row['id'];
		$_SESSION['last_activity'] = time();

		$response['status'] = true;
		$response['message'] = $saludo_login . ' ' . $row['user_firstname'] . '!';
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
