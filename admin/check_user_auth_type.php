<?php
require_once dirname(__DIR__) . '/config/env_reader.php';
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/security_functions.php';

header('Content-Type: application/json');
$response = ['status' => false, 'message' => '', 'auth_type' => 'password', 'user_id' => null];

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
{
    $response['message'] = 'Token de seguridad inválido';
    echo json_encode($response);
    exit();
}

// Verificar si se recibió el correo
if (!isset($_POST['username']) || empty($_POST['username']))
{
    $response['message'] = 'Por favor ingresa tu correo electrónico';
    echo json_encode($response);
    exit();
}

$username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);

// Verificar si el usuario existe y su tipo de autenticación
global $pdo;
$stmt = $pdo->prepare('SELECT id, admin_estado, tfa_enabled FROM admin WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user)
{
    $response['message'] = 'Usuario no encontrado';
    echo json_encode($response);
    exit();
}

// Verificar si la cuenta está deshabilitada
if ((int)$user['admin_estado'] == 1)
{
    $response['message'] = 'Tu cuenta ha sido deshabilitada';
    echo json_encode($response);
    exit();
}

// Verificar si tiene demasiados intentos fallidos
if (!checkLoginAttempts($username, env('MAIL_SUPPORT')))
{
    $response['message'] = 'Cuenta bloqueada temporalmente. Por favor contacta soporte';
    $response['blocked'] = true;
    echo json_encode($response);
    exit();
}

// Determinar tipo de autenticación
$response['status'] = true;
$response['user_id'] = $user['id'];

if ((int)$user['tfa_enabled'] == 1)
{
    $response['auth_type'] = '2fa';
    $response['message'] = 'Ingresa el código de verificación 2FA';
    $_SESSION['username'] = $username; // Guardar el nombre de usuario para futuros pasos
}
else
{
    $response['auth_type'] = 'password';
    $response['message'] = 'Ingresa tu contraseña';
    $_SESSION['username'] = $username; // Guardar el nombre de usuario para futuros pasos
}

echo json_encode($response);
exit();
