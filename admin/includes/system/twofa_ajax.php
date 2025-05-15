<?php
// filepath: c:\laragon\www\PH-JS-Kickstart\admin\includes\system\twofa_ajax.php

require_once '../session.php';
require_once '../functions/2fa_functions.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin']))
{
    echo json_encode(['status' => false, 'message' => 'No autorizado']);
    exit();
}

$adminId = $_SESSION['admin'];

// Obtener información del usuario
$sql = "SELECT username, tfa_enabled, tfa_secret FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$adminId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Determinar qué acción realizar basado en la solicitud AJAX
$action = $_POST['action'] ?? 'get_status';

switch ($action)
{
    case 'get_status':
        // Devolver el estado actual del 2FA
        echo json_encode([
            'status' => true,
            'tfa_enabled' => $user['tfa_enabled'] == 1,
            'has_secret' => !empty($user['tfa_secret']),
            'username' => $user['username']
        ]);
        break;

    case 'setup':
        // Generar un nuevo secreto para configurar 2FA
        $newSecret = generateTOTPSecret();
        $totp = createTOTP($newSecret, $user['username'], 'Sistema Admin');
        // Obtenemos la URI de aprovisionamiento para generar el QR con qrcode.js
        $provisioningUri = $totp->getProvisioningUri();

        // Guardar en sesión temporal (no en la base de datos aún)
        $_SESSION['temp_tfa_secret'] = $newSecret;

        echo json_encode([
            'status' => true,
            'secret' => $newSecret,
            'qr_uri' => $provisioningUri
        ]);
        break;

    case 'verify_setup':
        // Verificar código OTP para activar 2FA
        $otp = $_POST['otp'] ?? '';
        $secret = $_SESSION['temp_tfa_secret'] ?? '';

        if (empty($secret))
        {
            echo json_encode(['status' => false, 'message' => 'No se encontró un secreto temporal. Por favor reinicia el proceso.']);
            exit();
        }

        if (verifyOTP($secret, $otp))
        {
            // Generar códigos de respaldo
            $backupCodes = generateBackupCodes();

            // Guardar el secreto y activar 2FA
            $sql = "UPDATE admin SET tfa_secret = ?, tfa_enabled = 1, tfa_backup_codes = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$secret, json_encode($backupCodes), $adminId]);

            // Limpiar sesión temporal
            unset($_SESSION['temp_tfa_secret']);

            echo json_encode([
                'status' => true,
                'message' => 'Autenticación de dos factores activada correctamente.',
                'backup_codes' => $backupCodes
            ]);
        }
        else
        {
            echo json_encode(['status' => false, 'message' => 'El código ingresado es incorrecto. Inténtalo de nuevo.']);
        }
        break;

    case 'deactivate':
        // Desactivar 2FA
        $otp = $_POST['otp'] ?? '';

        if (verifyOTP($user['tfa_secret'], $otp))
        {
            // Desactivar 2FA pero mantener el secreto para reactivación fácil
            $sql = "UPDATE admin SET tfa_enabled = 0 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$adminId]);

            echo json_encode(['status' => true, 'message' => 'Autenticación de dos factores desactivada correctamente.']);
        }
        else
        {
            echo json_encode(['status' => false, 'message' => 'El código ingresado es incorrecto. Inténtalo de nuevo.']);
        }
        break;

    case 'regenerate_backup':
        // Regenerar códigos de respaldo
        $otp = $_POST['otp'] ?? '';

        if (verifyOTP($user['tfa_secret'], $otp))
        {
            // Generar nuevos códigos de respaldo
            $backupCodes = generateBackupCodes();

            // Guardar los nuevos códigos
            $sql = "UPDATE admin SET tfa_backup_codes = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([json_encode($backupCodes), $adminId]);

            echo json_encode([
                'status' => true,
                'message' => 'Códigos de respaldo regenerados correctamente.',
                'backup_codes' => $backupCodes
            ]);
        }
        else
        {
            echo json_encode(['status' => false, 'message' => 'El código ingresado es incorrecto. Inténtalo de nuevo.']);
        }
        break;

    case 'show_backup':
        // Mostrar códigos de respaldo existentes
        $otp = $_POST['otp'] ?? '';

        if (verifyOTP($user['tfa_secret'], $otp))
        {
            // Obtener códigos de respaldo
            $sql = "SELECT tfa_backup_codes FROM admin WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$adminId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && !empty($row['tfa_backup_codes']))
            {
                $backupCodes = json_decode($row['tfa_backup_codes'], true);
                echo json_encode([
                    'status' => true,
                    'backup_codes' => $backupCodes
                ]);
            }
            else
            {
                echo json_encode(['status' => false, 'message' => 'No hay códigos de respaldo disponibles.']);
            }
        }
        else
        {
            echo json_encode(['status' => false, 'message' => 'El código ingresado es incorrecto. Inténtalo de nuevo.']);
        }
        break;

    default:
        echo json_encode(['status' => false, 'message' => 'Acción no válida']);
}
