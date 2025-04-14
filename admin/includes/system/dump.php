<?php
include '../session.php';
$mail_support = env('MAIL_SUPPORT');

// Incluir mail_server.php cuando se necesite enviar por correo, verificando primero si la clase ya está definida
if (isset($_POST['mode']) && $_POST['mode'] == 'email')
{
	// Verificar si la clase PHPMailer\PHPMailer\Exception ya está definida
	if (!class_exists('PHPMailer\PHPMailer\PHPMailer', false))
	{
		include '../functions/mail_functions.php';
	}
	else
	{
		// Si ya está definida, solo incluir las funciones que necesitamos
		if (!function_exists('enviarCorreo'))
		{
			include '../mail_server.php';
		}
	}
}

// Obtener configuraciones de la base de datos desde el archivo principal
require_once __DIR__ . '/../../../config/db_conn.php';

// Reutilizar configuraciones desde db_conn.php en lugar de definirlas nuevamente

// Determinar el modo de operación (descargar o email)
$mode = isset($_POST['mode']) ? $_POST['mode'] : 'download';
$email = isset($_POST['email']) ? $_POST['email'] : $mail_support; // Usar el correo de soporte si no se proporciona otro

// Nombre del archivo temporal para almacenar el respaldo
$filename = __DIR__ . "/DB_BACKUP_" . date("Y-m-d-H-i-s") . ".sql";

// Función para probar el comando mysqldump y devolver el resultado
function tryMysqldump($command)
{
	exec($command, $output, $returnCode);
	return [$output, $returnCode];
}

// Función para intentar el respaldo con una configuración específica
function tryBackupWithConfig($config, $filename)
{
	$commands = [
		sprintf(
			'mysqldump --host=%s --user=%s --password=%s --column-statistics=0 %s > %s 2>&1',
			escapeshellarg($config['host']),
			escapeshellarg($config['user']),
			escapeshellarg($config['pass']),
			escapeshellarg($config['db']),
			escapeshellarg($filename)
		),
		sprintf(
			'mysqldump --host=%s --user=%s --password=%s --no-tablespaces %s > %s 2>&1',
			escapeshellarg($config['host']),
			escapeshellarg($config['user']),
			escapeshellarg($config['pass']),
			escapeshellarg($config['db']),
			escapeshellarg($filename)
		)
	];

	$lastOutput = [];
	$lastReturnCode = 1;

	foreach ($commands as $command)
	{
		list($output, $returnCode) = tryMysqldump($command);
		$lastOutput = $output;
		$lastReturnCode = $returnCode;

		if ($returnCode === 0)
		{
			return [true, null]; // Éxito
		}
	}

	return [false, $lastOutput]; // Fallo, devuelve el último mensaje de error
}

// Intentar realizar el respaldo 
list($success, $errorOutput) = tryBackupWithConfig($config, $filename);

// Verificar si el archivo se creó correctamente
if (file_exists($filename))
{
	if ($mode == 'download')
	{
		// Modo descarga - enviar archivo al navegador
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filename));
		flush();
		readfile($filename);
		unlink($filename); // Eliminar archivo después de descargarlo
		exit;
	}
	else if ($mode == 'email')
	{
		// Modo email - enviar por correo electrónico
		$asunto = "DB_BACKUP";
		$cuerpo = "Adjunto se encuentra el respaldo de la base de datos generado el " . date("Y-m-d H:i:s") . ".";
		$destinatarios = [$email]; // Usar el correo proporcionado
		$archivosAdjuntos = [$filename]; // Adjuntar el archivo generado

		try
		{
			enviarCorreo($asunto, $cuerpo, $destinatarios, $archivosAdjuntos);
			echo "El respaldo de la base de datos se ha enviado correctamente a: " . $email;
			unlink($filename); // Eliminar archivo después de enviarlo
		}
		catch (Exception $e)
		{
			echo "Error al enviar el correo: " . $e->getMessage();
			error_log("Error al enviar el correo: " . $e->getMessage());
			// Intentar eliminar el archivo en caso de error
			if (file_exists($filename))
			{
				unlink($filename);
			}
		}
	}
}
else
{
	if ($mode == 'download')
	{
		$_SESSION['error'] = 'Error: El archivo de respaldo no se creó correctamente.';
	}
	else
	{
		echo "Error: El archivo de respaldo no se creó correctamente.";
	}
}
