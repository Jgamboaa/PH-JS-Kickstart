<?php
include '../session.php';
$mail_support = env('MAIL_SUPPORT');
$ENV = env('APP_ENV');

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
	'db'   => env('DB_NAME'),
	'port' => env('DB_PORT')
];

// Determinar el modo de operación (descargar o email)
$mode  = isset($_POST['mode']) ? $_POST['mode'] : 'download';
$email = isset($_POST['email']) ? $_POST['email'] : $mail_support; // Usar el correo de soporte si no se proporciona otro

// Nombre del archivo temporal para almacenar el respaldo
$filename = __DIR__ . "/DB_BACKUP_" . date("Y-m-d-H-i-s") . ".sql";

try
{
	// Conexión PDO
	$dsn = 'mysql:host=' . $config['host'] . ';dbname=' . $config['db'] . ';charset=utf8mb4;port=' . $config['port'];
	$pdo = new PDO($dsn, $config['user'], $config['pass'], [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
	]);

	// Crear/abrir archivo destino
	$fh = fopen($filename, 'w');
	if ($fh === false)
	{
		throw new Exception('No se pudo crear el archivo de respaldo.');
	}

	// Cabecera del dump
	fwrite($fh, "-- Respaldo de base de datos: " . $config['db'] . " - " . date('Y-m-d H:i:s') . "\n");
	fwrite($fh, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
	fwrite($fh, "SET time_zone = '+00:00';\n");
	fwrite($fh, "SET foreign_key_checks = 0;\n");
	fwrite($fh, "START TRANSACTION;\n");
	fwrite($fh, "SET NAMES utf8mb4;\n\n");

	// Listar tablas y vistas
	$stmt = $pdo->prepare('SELECT TABLE_NAME, TABLE_TYPE FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :schema ORDER BY TABLE_NAME');
	$stmt->execute(['schema' => $config['db']]);
	$objects = $stmt->fetchAll();

	foreach ($objects as $obj)
	{
		$table = $obj['TABLE_NAME'];
		$type  = strtoupper($obj['TABLE_TYPE']);

		if ($type === 'BASE TABLE')
		{
			// Estructura de la tabla
			$createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
			$createSql = isset($createRow['Create Table']) ? $createRow['Create Table'] : (isset($createRow['Create Table']) ? $createRow['Create Table'] : '');

			fwrite($fh, "\n-- Estructura para la tabla `{$table}`\n");
			fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");
			if ($createSql !== '')
			{
				fwrite($fh, $createSql . ";\n\n");
			}

			// Columnas de la tabla
			$colStmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
			$columns = [];
			while ($col = $colStmt->fetch(PDO::FETCH_ASSOC))
			{
				$columns[] = $col['Field'];
			}

			// Datos de la tabla (en lotes)
			$countRow  = $pdo->query("SELECT COUNT(*) AS cnt FROM `{$table}`")->fetch(PDO::FETCH_ASSOC);
			$totalRows = isset($countRow['cnt']) ? (int)$countRow['cnt'] : 0;
			if ($totalRows > 0 && !empty($columns))
			{
				fwrite($fh, "-- Datos para la tabla `{$table}` ({$totalRows} filas)\n");
				$batchSize = 500;
				$offset    = 0;
				$colList   = "`" . implode("`,`", $columns) . "`";
				while (true)
				{
					$dataStmt = $pdo->query("SELECT * FROM `{$table}` LIMIT {$offset}, {$batchSize}");
					$rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
					if (!$rows)
					{
						break;
					}

					$valuesParts = [];
					foreach ($rows as $row)
					{
						$rowValues = [];
						foreach ($columns as $colName)
						{
							$value = array_key_exists($colName, $row) ? $row[$colName] : null;
							if (is_null($value))
							{
								$rowValues[] = 'NULL';
							}
							else
							{
								$rowValues[] = $pdo->quote($value);
							}
						}
						$valuesParts[] = '(' . implode(',', $rowValues) . ')';
					}

					fwrite($fh, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valuesParts) . ";\n");
					$offset += $batchSize;
				}
				fwrite($fh, "\n");
			}
		}
		elseif ($type === 'VIEW')
		{
			// Estructura de la vista
			$createRow = $pdo->query("SHOW CREATE VIEW `{$table}`")->fetch(PDO::FETCH_ASSOC);
			$createSql = isset($createRow['Create View']) ? $createRow['Create View'] : '';

			fwrite($fh, "\n-- Estructura para la vista `{$table}`\n");
			fwrite($fh, "DROP VIEW IF EXISTS `{$table}`;\n");
			if ($createSql !== '')
			{
				fwrite($fh, $createSql . ";\n\n");
			}
		}
	}

	// Triggers (si existen)
	$trgStmt = $pdo->prepare('SELECT TRIGGER_NAME FROM INFORMATION_SCHEMA.TRIGGERS WHERE TRIGGER_SCHEMA = :schema');
	$trgStmt->execute(['schema' => $config['db']]);
	$triggerNames = $trgStmt->fetchAll(PDO::FETCH_COLUMN);
	if ($triggerNames && count($triggerNames) > 0)
	{
		fwrite($fh, "\n-- Triggers\n");
		foreach ($triggerNames as $trigger)
		{
			$trg = $pdo->query("SHOW CREATE TRIGGER `{$trigger}`")->fetch(PDO::FETCH_ASSOC);
			if (isset($trg['Create Trigger']) && $trg['Create Trigger'] !== '')
			{
				fwrite($fh, "DROP TRIGGER IF EXISTS `{$trigger}`;\n");
				fwrite($fh, $trg['Create Trigger'] . ";\n\n");
			}
			elseif (isset($trg['SQL Original Statement']) && $trg['SQL Original Statement'] !== '')
			{
				// Fallback a la declaración original si el formato es distinto
				fwrite($fh, "DROP TRIGGER IF EXISTS `{$trigger}`;\n");
				fwrite($fh, "DELIMITER ;;\n" . $trg['SQL Original Statement'] . " ;;\nDELIMITER ;\n\n");
			}
		}
	}

	fwrite($fh, "SET foreign_key_checks = 1;\n");
	fwrite($fh, "COMMIT;\n");
	fclose($fh);

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
	error_log("Error al realizar respaldo: " . $e->getMessage());

	if ($mode == 'download')
	{
		echo json_encode(['status' => false, 'message' => 'Error al crear backup: ' . $e->getMessage()]);
	}
	else
	{
		echo json_encode(['status' => false, 'message' => 'Error al crear backup: ' . $e->getMessage()]);
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
			echo json_encode(['status' => true, 'message' => "El respaldo de la base de datos se ha enviado correctamente a: " . htmlspecialchars($email)]);
			unlink($filename); // Eliminar archivo después de enviarlo
		}
		catch (Exception $e)
		{
			echo json_encode(['status' => false, 'message' => "Error al enviar el correo: " . $e->getMessage()]);
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
		echo json_encode(['status' => false, 'message' => 'Error: El archivo de respaldo no se creó correctamente.']);
		exit;
	}
	else
	{
		echo json_encode(['status' => false, 'message' => 'Error: El archivo de respaldo no se creó correctamente.']);
	}
}
