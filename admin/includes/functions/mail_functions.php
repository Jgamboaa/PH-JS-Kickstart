<?php
// Incluir el lector de variables de entorno
require_once __DIR__ . '/../../../config/env_reader.php';

// Obtener las configuraciones de correo desde el .env
$host_mail = env('MAIL_HOST');
$username_mail = env('MAIL_USERNAME');
$username_name_mail = env('MAIL_NAME');
$password_mail = env('MAIL_PASSWORD');
$port = env('MAIL_PORT');
$encryption = env('MAIL_ENCRYPTION');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';


if (!function_exists('enviarCorreo'))
{
    function enviarCorreo($asunto, $cuerpo, $destinatarios, $archivosAdjuntos)
    {
        global $host_mail, $username_mail, $password_mail, $port, $username_name_mail, $encryption;

        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8'; // Establecer el juego de caracteres a UTF-8
        try
        {
            // Configuración del servidor SMTP
            $mail->SMTPDebug = 0; // Cambiar a SMTP::DEBUG_SERVER para más detalles
            $mail->isSMTP();
            $mail->Host = $host_mail;
            $mail->SMTPAuth = true;
            $mail->Username = $username_mail;
            $mail->Password = $password_mail;
            $mail->SMTPSecure = ($encryption === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS; // Usa STARTTLS para 'tls' y SMTPS en otros casos
            $mail->Port = $port; // Puerto 

            // Destinatarios
            $mail->setFrom($username_mail, $username_name_mail);
            foreach ($destinatarios as $destinatario)
            {
                $mail->addAddress($destinatario);
            }
            // $mail->addBCC($username_mail, $username_name_mail);

            // Archivos adjuntos
            foreach ($archivosAdjuntos as $archivoAdjunto)
            {
                $mail->addAttachment($archivoAdjunto);
            }

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $cuerpo;

            // Enviar el correo
            $mail->send();

            // Si todo va bien, retornamos true
            return true;
        }
        catch (Exception $e)
        {
            // En caso de error, puedes registrar el error o asignarlo a una variable de sesión
            $_SESSION['error'] = 'Error al enviar el mensaje: ' . $mail->ErrorInfo;

            // Retornamos false para indicar que ocurrió un error
            return false;
        }
    }
}


//correo de prueba


// $asunto = "Prueba de correo desde PHP";
// $cuerpo = "Este es un correo de prueba enviado desde PHP.";


// $destinatarios = ['isai.gamboa@nelixia.com'];
// $archivosAdjuntos = [];

// $enviado = enviarCorreo($asunto, $cuerpo, $destinatarios, $archivosAdjuntos);
// if ($enviado)
// {
//     echo "Correo enviado con éxito.";
// }
// else
// {
//     echo "Error al enviar el correo: " . $_SESSION['error'];
// }
