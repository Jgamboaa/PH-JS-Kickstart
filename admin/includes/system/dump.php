<?php
include '../session.php';
$mail_support = env('MAIL_SUPPORT');
$ENV = env('APP_ENV', 'production'); // Valor por defecto 'production' si no está definido

require_once __DIR__ . '/../../../vendor/autoload.php';

// Incluir mail_server.php cuando se necesite enviar por correo, verificando primero si la clase ya está definida
if (isset($_POST['mode']) && $_POST['mode'] == 'email')
{
	// Verificar si la clase PHPMailer\PHPMailer\PHPMailer ya está definida
	if (!class_exists('PHPMailer\PHPMailer\PHPMailer', false) || !function_exists('enviarCorreo'))
	{
		include '../functions/mail_functions.php';
	}
}

$config = [
	'host' => env('DB_HOST'),
	'user' => env('DB_USER'),
	'pass' => env('DB_PASS'),
	'db'   => env('DB_NAME')
];

// Determinar el modo de operación (descargar o email)
$mode  = isset($_POST['mode']) ? $_POST['mode'] : 'download';
$email = isset($_POST['email']) ? $_POST['email'] : $mail_support; // Usar el correo de soporte si no se proporciona otro

// Nombre del archivo temporal para almacenar el respaldo
$filename = __DIR__ . "/DB_BACKUP_" . date("Y-m-d-H-i-s") . ".sql";

use Spatie\DbDumper\Databases\MySql;

try
{
	$dumper = MySql::create()
		->setHost($config['host'])
		->setDbName($config['db'])
		->setUserName($config['user'])
		->setPassword($config['pass'])
		->addExtraOption('--single-transaction');

	// Activar opciones adicionales solo en entorno local
	if ($ENV === 'local')
	{
		$dumper->addExtraOption('--routines')
			->addExtraOption('--events')
			->addExtraOption('--triggers');
	}

	$dumper->dumpToFile($filename);

	// Eliminar DEFINERs del archivo dump
	if (file_exists($filename))
	{
		$dump = file_get_contents($filename);
		// Quita cualquier fragmento DEFINER=`usuario`@`host`
		$dump = preg_replace('/DEFINER=`[^`]+`@`[^`]+`/i', '', $dump);
		file_put_contents($filename, $dump);
		$success = true;
	}
}
catch (Exception $e)
{
	$success = false;
	error_log("Error al realizar respaldo con DB-Dumper: " . $e->getMessage());

	if ($mode == 'download')
	{
		$_SESSION['error'] = 'Error al crear backup con DB-Dumper: ' . $e->getMessage();
		header('Location: ../../users.php');
		exit;
	}
	else
	{
		echo 'Error al crear backup con DB-Dumper: ' . $e->getMessage();
		exit;
	}
}

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
	elseif ($mode == 'email')
	{
		// Modo email - enviar por correo electrónico
		$asunto           = "DB_BACKUP";
		$cuerpo           = "Adjunto se encuentra el respaldo de la base de datos generado el " . date("Y-m-d H:i:s") . ".";
		$destinatarios    = [$email];      // Usar el correo proporcionado
		$archivosAdjuntos = [$filename];   // Adjuntar el archivo generado

		try
		{
			enviarCorreo($asunto, $cuerpo, $destinatarios, $archivosAdjuntos);
			echo "El respaldo de la base de datos se ha enviado correctamente a: " . htmlspecialchars($email);
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
		header('Location: ../../users.php');
		exit;
	}
	else
	{
		echo "Error: El archivo de respaldo no se creó correctamente.";
	}
}
