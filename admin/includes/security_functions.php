<?php
include 'functions/mail_functions.php';
date_default_timezone_set('America/Guatemala');

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token']))
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function checkLoginAttempts($username)
{
    global $conn;
    $sql = "SELECT login_attempts, last_attempt FROM login_attempts WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0)
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $bloqueo = false;
        $tiempoBloqueo = 0;

        if ($row['login_attempts'] >= 3 && time() - strtotime($row['last_attempt']) < 180)
        {
            $bloqueo = true;
            $tiempoBloqueo = 3;
        }
        elseif ($row['login_attempts'] >= 6 && time() - strtotime($row['last_attempt']) < 300)
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
                    <li><strong>Intentos fallidos:</strong> {$row['login_attempts']}</li>
                    <li><strong>Tiempo de bloqueo:</strong> {$tiempoBloqueo} minutos</li>
                    <li><strong>IP:</strong> {$_SERVER['REMOTE_ADDR']}</li>
                    <li><strong>Fecha y hora:</strong> " . date('d/m/Y H:i:s') . "</li>
                </ul>
            ";

            $destinatarios = ['isai.gamboa@nelixia.com'];
            enviarCorreo($asunto, $cuerpo, $destinatarios, []);

            return false; // bloqueado
        }
    }
    return true; // permitido
}

function updateLoginAttempts($username)
{
    global $conn;
    $sql = "INSERT INTO login_attempts (username, login_attempts, last_attempt) 
            VALUES (?, 1, NOW()) 
            ON DUPLICATE KEY UPDATE 
            login_attempts = login_attempts + 1, 
            last_attempt = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
}

function resetLoginAttempts($username)
{
    global $conn;
    $sql = "DELETE FROM login_attempts WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
}

function logLoginActivity($username, $success)
{
    global $conn;
    $sql = "INSERT INTO login_logs (username, status, ip_address) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $status = $success ? 'success' : 'failed';
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->execute([$username, $status, $ip]);
}
