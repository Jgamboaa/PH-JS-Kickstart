<?php
require_once 'includes/session_config.php';
require_once dirname(__DIR__) . '/config/db_conn.php';
require_once 'includes/functions/2fa_functions.php';
require_once 'includes/security_functions.php';

// Asegurar que la solicitud sea mediante AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
{
    exit(json_encode(['status' => false, 'message' => 'Acceso no autorizado']));
}

// Verificar CSRF token - usando comprobación directa de sesión
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
{
    exit(json_encode(['status' => false, 'message' => 'Token de seguridad inválido']));
}

// Manejar solicitud para cancelar verificación 2FA
if (isset($_POST['action']) && $_POST['action'] === 'cancel')
{
    // Eliminar variables de sesión relacionadas con 2FA
    unset($_SESSION['2fa_pending']);
    unset($_SESSION['2fa_user_id']);
    unset($_SESSION['setup_2fa_pending']); // También limpiamos otras variables relacionadas con 2FA
    unset($_SESSION['setup_2fa_user_id']);
    unset($_SESSION['setup_2fa_username']);
    unset($_SESSION['temp_tfa_secret']);

    exit(json_encode(['status' => true, 'message' => 'Verificación cancelada']));
}

// Verificar que exista un código y un ID de usuario
if (!isset($_POST['code']) || !isset($_POST['user_id']))
{
    exit(json_encode(['status' => false, 'message' => 'Datos incompletos']));
}

$code = trim($_POST['code']);
$userId = (int)$_POST['user_id'];
$useBackupCode = isset($_POST['backup_mode']) && $_POST['backup_mode'] == 1;

// Si no hay código, error
if (empty($code))
{
    exit(json_encode(['status' => false, 'message' => 'Por favor ingresa un código']));
}

// Obtener información del usuario
$sql = "SELECT id, username, tfa_secret, user_firstname, admin_gender, last_login FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user)
{
    exit(json_encode(['status' => false, 'message' => 'Usuario no encontrado']));
}

// Realizar verificación según el tipo de código
$verification_success = false;

if ($useBackupCode)
{
    // Verificar código de respaldo
    $verification_success = verifyBackupCode($userId, $code);
    if (!$verification_success)
    {
        exit(json_encode(['status' => false, 'message' => 'Código de respaldo inválido']));
    }
}
else
{
    // Verificar código TOTP
    $verification_success = verifyOTP($user['tfa_secret'], $code);
    if (!$verification_success)
    {
        exit(json_encode(['status' => false, 'message' => 'Código de verificación inválido']));
    }
}

// Si la verificación es exitosa, completar el inicio de sesión
if ($verification_success)
{
    // Actualizar último login
    $sql = "UPDATE admin SET last_login = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);

    // Eliminar estado pendiente 2FA
    unset($_SESSION['2fa_pending']);
    unset($_SESSION['2fa_user_id']);

    // Establecer sesión normal
    $_SESSION['admin'] = $userId;
    $_SESSION['last_activity'] = time();

    // Preparar mensaje de bienvenida
    if (empty($user['last_login']))
    {
        $saludo_login = ($user['admin_gender'] == '0') ? "¡Bienvenido al sistema" : "¡Bienvenida al sistema";
    }
    else
    {
        $saludo_login = ($user['admin_gender'] == '0') ? "¡Bienvenido de nuevo" : "¡Bienvenida de nuevo";
    }

    // Devolver éxito
    exit(json_encode([
        'status' => true,
        'message' => $saludo_login . ' ' . $user['user_firstname'] . '!',
        'redirect_url' => 'home.php'
    ]));
}

// Si llegamos aquí, algo salió mal
exit(json_encode(['status' => false, 'message' => 'Error en la verificación']));
