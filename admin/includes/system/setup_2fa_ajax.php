<?php
require_once '../session_config.php';
require_once dirname(__DIR__, 3) . '/config/db_conn.php';
require_once '../functions/2fa_functions.php';
$nombre_sistema = env('APP_NAME');

// Verificar si hay una sesión pendiente de configuración 2FA
if (!isset($_SESSION['setup_2fa_pending']) || !isset($_SESSION['setup_2fa_user_id']))
{
    echo json_encode(['status' => false, 'message' => 'No autorizado']);
    exit();
}

$action = $_POST['action'] ?? 'get_qrcode';

switch ($action)
{
    case 'get_qrcode':
        $userId = $_SESSION['setup_2fa_user_id'];
        $username = $_SESSION['setup_2fa_username'] ?? '';

        // Obtener información del usuario si no tenemos el username
        if (empty($username))
        {
            $sql = "SELECT username FROM admin WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $username = $result['username'] ?? '';
        }

        // Generar un nuevo secreto para 2FA
        $newSecret = generateTOTPSecret();
        $totp = createTOTP($newSecret, $username, $nombre_sistema);
        $provisioningUri = $totp->getProvisioningUri();

        // Guardar el secreto en la sesión temporalmente
        $_SESSION['temp_tfa_secret'] = $newSecret;

        echo json_encode([
            'status' => true,
            'qr_uri' => $provisioningUri,
            'secret' => $newSecret
        ]);
        break;

    case 'cancel_setup':
        // Limpiar datos de sesión relacionados con la configuración
        unset($_SESSION['setup_2fa_pending']);
        unset($_SESSION['setup_2fa_user_id']);
        unset($_SESSION['setup_2fa_username']);
        unset($_SESSION['temp_tfa_secret']);

        echo json_encode(['status' => true, 'message' => 'Configuración cancelada']);
        break;

    default:
        echo json_encode(['status' => false, 'message' => 'Acción no válida']);
}
