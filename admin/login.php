<?php
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '../config/db_conn.php';
require_once 'includes/security_functions.php';

header('Content-Type: application/json');
$response = ['status' => false, 'message' => '', 'redirect' => false];

if (isset($_POST['login']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
	$password = $_POST['password'];

	if (checkLoginAttempts($username))
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
else
{
	$response['message'] = 'Acceso inválido';
}

echo json_encode($response);
exit();
