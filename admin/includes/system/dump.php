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

// Crear el array de configuración con las variables de db_conn.php
$config = [
	'host' => $host,
	'user' => $user,
	'pass' => $pass,
	'db'   => $db
];

// Determinar el modo de operación (descargar o email)
$mode = isset($_POST['mode']) ? $_POST['mode'] : 'download';
$email = isset($_POST['email']) ? $_POST['email'] : $mail_support; // Usar el correo de soporte si no se proporciona otro

// Nombre del archivo temporal para almacenar el respaldo
$filename = __DIR__ . "/DB_BACKUP_" . date("Y-m-d-H-i-s") . ".sql";

// Función para respaldar la base de datos usando PDO
function backupDatabaseWithPDO($conn, $filename)
{
	try
	{
		$tables = [];
		$result = $conn->query("SHOW TABLES");
		while ($row = $result->fetch(PDO::FETCH_NUM))
		{
			$tables[] = $row[0];
		}

		$return = "-- Respaldo de base de datos generado el " . date("Y-m-d H:i:s") . "\n";
		$return .= "-- Usando PDO\n\n";

		// Obtener y guardar estructura de tablas
		foreach ($tables as $table)
		{
			$result = $conn->query("SHOW CREATE TABLE `$table`");
			$row = $result->fetch();

			$return .= "\n\n-- Estructura de la tabla `$table`\n\n";
			$return .= "DROP TABLE IF EXISTS `$table`;\n";
			$return .= $row['Create Table'] . ";\n\n";

			// Obtener datos
			$result = $conn->query("SELECT * FROM `$table`");
			$columnCount = $result->columnCount();

			if ($result->rowCount() > 0)
			{
				$return .= "-- Volcado de datos para la tabla `$table`\n";

				while ($row = $result->fetch(PDO::FETCH_NUM))
				{
					$return .= "INSERT INTO `$table` VALUES (";
					for ($j = 0; $j < $columnCount; $j++)
					{
						if (isset($row[$j]))
						{
							$row[$j] = addslashes($row[$j]);
							$row[$j] = str_replace("\n", "\\n", $row[$j]);
							$return .= '"' . $row[$j] . '"';
						}
						else
						{
							$return .= 'NULL';
						}
						if ($j < ($columnCount - 1))
						{
							$return .= ',';
						}
					}
					$return .= ");\n";
				}
			}
			$return .= "\n\n";
		}

		// Guardar archivo
		if (file_put_contents($filename, $return))
		{
			return [true, null];
		}
		else
		{
			return [false, ["Error al escribir en el archivo de respaldo"]];
		}
	}
	catch (Exception $e)
	{
		return [false, ["Error en PDO: " . $e->getMessage()]];
	}
}

// Realizar respaldo usando PDO
list($success, $pdoError) = backupDatabaseWithPDO($conn, $filename);

// Registrar resultado del respaldo
if (!$success)
{
	error_log("Error al realizar respaldo con PDO: " . print_r($pdoError, true));
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
