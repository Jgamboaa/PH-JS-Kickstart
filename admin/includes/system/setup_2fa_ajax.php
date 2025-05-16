<?php
require_once dirname(dirname(dirname(__DIR__))) . '/config/db_conn.php';
require_once dirname(dirname(__FILE__)) . '/session_config.php';
require_once dirname(dirname(__FILE__)) . '/functions/2fa_functions.php';
require_once dirname(dirname(__FILE__)) . '/security_functions.php';

header('Content-Type: application/json');
$response = ['status' => false, 'message' => ''];

// Asegurar que la solicitud sea mediante AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
{
    exit(json_encode(['status' => false, 'message' => 'Acceso no autorizado']));
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
{
    exit(json_encode(['status' => false, 'message' => 'Token de seguridad inválido']));
}

// Procesar solicitudes
if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case 'get_qrcode':
            // Generar un nuevo secreto TOTP
            $secret = generateTOTPSecret();

            // Guardar el secreto en la sesión para usar durante la verificación
            $_SESSION['temp_tfa_secret'] = $secret;

            // Asegurar que la sesión marque que hay una configuración de 2FA pendiente
            $_SESSION['setup_2fa_pending'] = true;

            // Si el user_id no está en la sesión pero se proporcionó en la solicitud
            if (!isset($_SESSION['setup_2fa_user_id']) && isset($_POST['user_id']))
            {
                $_SESSION['setup_2fa_user_id'] = (int)$_POST['user_id'];
            }

            // Obtener el nombre de usuario para el QR
            $username = isset($_SESSION['setup_2fa_username']) ? $_SESSION['setup_2fa_username'] : 'Usuario';

            // Crear el objeto TOTP
            $totp = createTOTP($secret, $username, 'Sistema Admin');
            $qrCodeUri = $totp->getProvisioningUri();

            echo json_encode([
                'status' => true,
                'secret' => $secret,
                'qr_uri' => $qrCodeUri
            ]);
            break;

        case 'cancel_setup':
            // Limpiar variables de sesión relacionadas con la configuración 2FA
            unset($_SESSION['setup_2fa_pending']);
            unset($_SESSION['setup_2fa_user_id']);
            unset($_SESSION['setup_2fa_username']);
            unset($_SESSION['temp_tfa_secret']);

            echo json_encode(['status' => true, 'message' => 'Configuración cancelada']);
            break;

        default:
            echo json_encode(['status' => false, 'message' => 'Acción no reconocida']);
    }
}
else
{
    echo json_encode(['status' => false, 'message' => 'Acción no especificada']);
}
