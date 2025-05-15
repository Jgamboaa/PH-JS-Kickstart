<?php
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/security_functions.php';
$mail_support = env('MAIL_SUPPORT');
header('Content-Type: application/json');
$response = ['status' => false, 'message' => '', 'redirect' => false];

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
					session_regenerate_id(true);
					logLoginActivity($username, true);

					// Verificar si el usuario tiene 2FA activado
					if ($row['tfa_enabled'] == 1)
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
		session_regenerate_id(true);
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
