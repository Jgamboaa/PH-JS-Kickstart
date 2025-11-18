<?php
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

/**
 * Genera un nuevo secreto para TOTP
 * @return string Secret en formato Base32
 */
function generateTOTPSecret()
{
    $secret = random_bytes(32);
    return Base32::encodeUpper($secret);
}

/**
 * Genera una nueva instancia de TOTP con el secreto dado
 * @param string $secret La clave secreta en formato Base32
 * @param string $label Etiqueta para el código QR (normalmente el nombre de usuario)
 * @param string $issuer Nombre de la aplicación/sistema
 * @return TOTP Instancia de TOTP configurada
 */
function createTOTP($secret, $label, $issuer = 'Sistema')
{
    $totp = TOTP::create($secret);
    $totp->setLabel($label);
    $totp->setIssuer($issuer);
    $totp->setDigits(6); // 6 dígitos es el estándar para la mayoría de aplicaciones
    $totp->setPeriod(30); // Periodo de 30 segundos es el estándar

    return $totp;
}

/**
 * Verifica un código OTP para la autenticación
 * @param string $secret La clave secreta del usuario
 * @param string $otp El código proporcionado por el usuario
 * @return bool True si el código es válido, False en caso contrario
 */
function verifyOTP($secret, $otp)
{
    $totp = TOTP::create($secret);
    return $totp->verify($otp);
}

/**
 * Genera códigos de respaldo para el usuario
 * @param int $count Número de códigos a generar
 * @return array Array con los códigos generados
 */
function generateBackupCodes($count = 10)
{
    $codes = [];
    for ($i = 0; $i < $count; $i++)
    {
        $codes[] = substr(bin2hex(random_bytes(4)), 0, 8);
    }
    return $codes;
}

/**
 * Verifica si un código de respaldo es válido y lo elimina si es así
 * @param int $adminId ID del administrador
 * @param string $code Código de respaldo a verificar
 * @return bool True si el código es válido, False en caso contrario
 */
function verifyBackupCode($adminId, $code)
{
    // Usar PDO para obtener la información del administrador
    global $pdo;

    $stmt = $pdo->prepare('SELECT id, tfa_backup_codes FROM admin WHERE id = :id');
    $stmt->execute([':id' => $adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || empty($admin['tfa_backup_codes']))
    {
        return false;
    }

    $backupCodes = json_decode($admin['tfa_backup_codes'], true);
    if (!is_array($backupCodes))
    {
        return false;
    }

    // Buscar y eliminar el código si existe
    $key = array_search($code, $backupCodes);
    if ($key !== false)
    {
        unset($backupCodes[$key]);

        // Actualizar los códigos en la base de datos
        $stmt = $pdo->prepare('UPDATE admin SET tfa_backup_codes = :codes WHERE id = :id');
        $stmt->execute([
            ':codes' => json_encode(array_values($backupCodes)),
            ':id'    => $adminId,
        ]);

        return true;
    }

    return false;
}
