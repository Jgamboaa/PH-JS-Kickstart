<?php
include 'functions/mail_functions.php';

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkLoginAttempts($username, $mail_support)
{
    // Usar PDO y la tabla login_attempts
    global $pdo;

    $stmt = $pdo->prepare('SELECT login_attempts, last_attempt FROM login_attempts WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $loginAttempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($loginAttempt)
    {
        $bloqueo = false;
        $tiempoBloqueo = 0;
        $lastAttemptTime = $loginAttempt['last_attempt'] ? strtotime($loginAttempt['last_attempt']) : 0;

        if ($loginAttempt['login_attempts'] >= 3 && time() - $lastAttemptTime < 180)
        {
            $bloqueo = true;
            $tiempoBloqueo = 3;
        }
        elseif ($loginAttempt['login_attempts'] >= 6 && time() - $lastAttemptTime < 300)
        {
            $bloqueo = true;
            $tiempoBloqueo = 5;
        }

        if ($bloqueo)
        {
            // Preparar y enviar el correo de notificación
            $dominio = $_SERVER['HTTP_HOST'];
            $asunto = "Usuario Bloqueado - {$dominio}";
            $cuerpo = "
                <h2>Alerta de Seguridad - Usuario Bloqueado</h2>
                <p>Se ha detectado un bloqueo de cuenta con los siguientes detalles:</p>
                <ul>
                    <li><strong>Usuario:</strong> {$username}</li>
                    <li><strong>Intentos fallidos:</strong> {$loginAttempt['login_attempts']}</li>
                    <li><strong>Tiempo de bloqueo:</strong> {$tiempoBloqueo} minutos</li>
                    <li><strong>IP:</strong> {$_SERVER['REMOTE_ADDR']}</li>
                    <li><strong>Fecha y hora:</strong> " . date('d/m/Y H:i:s') . "</li>
                </ul>
            ";

            $destinatarios = [$mail_support];
            enviarCorreo($asunto, $cuerpo, $destinatarios, []);

            return false; // bloqueado
        }
    }
    return true; // permitido
}

function updateLoginAttempts($username)
{
    global $pdo;

    // Comprobar si ya existe un registro para este usuario
    $stmt = $pdo->prepare('SELECT id, login_attempts FROM login_attempts WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $loginAttempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($loginAttempt)
    {
        // Actualizar intento existente
        $stmt = $pdo->prepare('UPDATE login_attempts SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = :id');
        $stmt->execute([':id' => $loginAttempt['id']]);
    }
    else
    {
        // Crear nuevo registro de intento
        $stmt = $pdo->prepare('INSERT INTO login_attempts (username, login_attempts, last_attempt) VALUES (:username, 1, NOW())');
        $stmt->execute([':username' => $username]);
    }
}

function resetLoginAttempts($username)
{
    global $pdo;

    $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE username = :username');
    $stmt->execute([':username' => $username]);
}

function logLoginActivity($username, $success)
{
    global $pdo;

    $stmt = $pdo->prepare('INSERT INTO login_logs (username, status, ip_address, created_at) VALUES (:username, :status, :ip_address, NOW())');
    $stmt->execute([
        ':username'    => $username,
        ':status'      => $success ? 'success' : 'failed',
        ':ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
