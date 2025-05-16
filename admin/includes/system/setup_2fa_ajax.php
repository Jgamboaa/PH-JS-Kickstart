<?php
require_once dirname(dirname(__DIR__)) . '/includes/session_config.php';
require_once dirname(dirname(dirname(__DIR__))) . '/config/db_conn.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions/2fa_functions.php';

header('Content-Type: application/json');
$response = ['status' => false, 'message' => ''];

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
{
    $response['message'] = 'Error de seguridad: token CSRF inválido';
    echo json_encode($response);
    exit();
}

if (isset($_POST['action']))
{
    switch ($_POST['action'])
    {
        case 'get_qrcode':
            // Generar un nuevo secreto temporal y almacenarlo en la sesión
            $secret = generateTOTPSecret();
            $_SESSION['temp_tfa_secret'] = $secret;

            // Determinar el nombre de usuario a usar
            $username = isset($_SESSION['setup_2fa_username'])
                ? $_SESSION['setup_2fa_username']
                : (isset($_SESSION['username']) ? $_SESSION['username'] : 'usuario');

            // Crear objeto TOTP
            $totp = createTOTP($secret, $username, 'Sistema Admin');
            $provisioningUri = $totp->getProvisioningUri();

            $response = [
                'status' => true,
                'secret' => $secret,
                'qr_uri' => $provisioningUri
            ];
            break;

        case 'cancel_setup':
            // Limpiar variables de sesión relacionadas con la configuración 2FA
            unset($_SESSION['temp_tfa_secret']);
            unset($_SESSION['setup_2fa_pending']);
            unset($_SESSION['setup_2fa_user_id']);
            unset($_SESSION['setup_2fa_username']);

            $response['status'] = true;
            $response['message'] = 'Configuración cancelada';
            break;

        case 'check_session_status':
            // Devolver el estado actual de la sesión para depuración
            $response['status'] = true;
            $response['session_data'] = [
                'setup_2fa_pending' => isset($_SESSION['setup_2fa_pending']) ? true : false,
                'setup_2fa_user_id' => isset($_SESSION['setup_2fa_user_id']) ? $_SESSION['setup_2fa_user_id'] : null,
                'temp_tfa_secret' => isset($_SESSION['temp_tfa_secret']) ? 'exists' : null,
                'admin' => isset($_SESSION['admin']) ? $_SESSION['admin'] : null,
            ];
            break;

        default:
            $response['message'] = 'Acción desconocida';
            break;
    }
}
else
{
    $response['message'] = 'Ninguna acción especificada';
}

echo json_encode($response);
exit();
