<?php
include 'functions/mail_functions.php';

// Usar RedBeanPHP
use RedBeanPHP\R as R;

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
    $loginAttempt = R::findOne('loginattempts', 'username = ?', [$username]);

    if ($loginAttempt)
    {
        $bloqueo = false;
        $tiempoBloqueo = 0;
        $lastAttemptTime = strtotime($loginAttempt->last_attempt);

        if ($loginAttempt->login_attempts >= 3 && time() - $lastAttemptTime < 180)
        {
            $bloqueo = true;
            $tiempoBloqueo = 3;
        }
        elseif ($loginAttempt->login_attempts >= 6 && time() - $lastAttemptTime < 300)
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
                    <li><strong>Intentos fallidos:</strong> {$loginAttempt->login_attempts}</li>
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
    $loginAttempt = R::findOne('loginattempts', 'username = ?', [$username]);

    if ($loginAttempt)
    {
        // Actualizar intento existente
        $loginAttempt->login_attempts += 1;
        $loginAttempt->last_attempt = R::isoDateTime();
    }
    else
    {
        // Crear nuevo registro de intento
        $loginAttempt = R::dispense('loginattempts');
        $loginAttempt->username = $username;
        $loginAttempt->login_attempts = 1;
        $loginAttempt->last_attempt = R::isoDateTime();
    }

    R::store($loginAttempt);
}

function resetLoginAttempts($username)
{
    $loginAttempts = R::find('loginattempts', 'username = ?', [$username]);
    R::trashAll($loginAttempts);
}

function logLoginActivity($username, $success)
{
    $loginLog = R::dispense('loginlogs');
    $loginLog->username = $username;
    $loginLog->status = $success ? 'success' : 'failed';
    $loginLog->ip_address = $_SERVER['REMOTE_ADDR'];
    $loginLog->created_at = R::isoDateTime();
    R::store($loginLog);
}
