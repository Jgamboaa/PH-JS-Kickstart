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
$mode  = isset($_POST['mode']) ? $_POST['mode'] : 'download';
$email = isset($_POST['email']) ? $_POST['email'] : $mail_support; // Usar el correo de soporte si no se proporciona otro

// Nombre del archivo temporal para almacenar el respaldo
$filename = __DIR__ . "/DB_BACKUP_" . date("Y-m-d-H-i-s") . ".sql";

/**
 * Función para respaldar la base de datos usando PDO.
 * Genera INSERTs en lotes de $batchSize filas cada uno.
 */
function backupDatabaseWithPDO($conn, $filename)
{
	try
	{
		// Función para remover cláusulas DEFINER de vistas, funciones, etc.
		function removeDefiner($createStatement)
		{
			return preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s/', '', $createStatement);
		}

		// Obtenemos la lista de tablas
		$tables = [];
		$result = $conn->query("SHOW TABLES");
		while ($row = $result->fetch(PDO::FETCH_NUM))
		{
			$tables[] = $row[0];
		}

		// Cabecera del archivo SQL de respaldo
		$output  = "-- Respaldo de base de datos generado el " . date("Y-m-d H:i:s") . "\n";
		$output .= "-- Usando PDO\n\n";
		$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

		// Definir tamaño de lote: cuántas filas por INSERT máximo
		$batchSize = 1000;

		// ===== 1) Respaldar estructura y datos de cada tabla =====
		foreach ($tables as $table)
		{
			// 1.a) Estructura de la tabla
			$stmtCreate = $conn->query("SHOW CREATE TABLE `$table`");
			$rowCreate  = $stmtCreate->fetch(PDO::FETCH_ASSOC);

			$output .= "\n\n-- Estructura de la tabla `$table`\n\n";
			$output .= "DROP TABLE IF EXISTS `$table`;\n";
			$output .= $rowCreate['Create Table'] . ";\n\n";

			// 1.b) Datos de la tabla: fetchAll para armar el lote
			$dataStmt    = $conn->query("SELECT * FROM `$table`");
			$columnCount = $dataStmt->columnCount();
			$allRows     = $dataStmt->fetchAll(PDO::FETCH_NUM);

			if (count($allRows) > 0)
			{
				$output .= "-- Volcado de datos para la tabla `$table`\n";

				// Preparar array de cadenas con cada fila escapada
				$rowsEscaped = [];
				foreach ($allRows as $rowValues)
				{
					$escapedValues = [];
					for ($j = 0; $j < $columnCount; $j++)
					{
						if (isset($rowValues[$j]))
						{
							$val = addslashes($rowValues[$j]);
							$val = str_replace("\n", "\\n", $val);
							$escapedValues[] = '"' . $val . '"';
						}
						else
						{
							$escapedValues[] = 'NULL';
						}
					}
					$rowsEscaped[] = "(" . implode(",", $escapedValues) . ")";
				}

				// Fragmentar $rowsEscaped en bloques de $batchSize
				$chunks = array_chunk($rowsEscaped, $batchSize, true);
				foreach ($chunks as $chunkIndex => $chunkRows)
				{
					$output .= "INSERT INTO `$table` VALUES \n";
					$output .= implode(",\n", $chunkRows) . ";\n\n";
				}
			}
			else
			{
				// Si no hay datos, dejamos una línea en blanco
				$output .= "\n";
			}
		}

		// ===== 2) Respaldar Vistas =====
		$output .= "\n\n-- --------------------------------------------------------\n";
		$output .= "-- Vistas\n";
		$output .= "-- --------------------------------------------------------\n\n";

		$viewResult = $conn->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
		while ($row = $viewResult->fetch(PDO::FETCH_NUM))
		{
			$viewName   = $row[0];
			$stmtView   = $conn->query("SHOW CREATE VIEW `$viewName`");
			$viewCreate = $stmtView->fetch(PDO::FETCH_ASSOC);
			$createView = removeDefiner($viewCreate['Create View']);

			$output .= "\n-- Estructura para la vista `$viewName`\n";
			$output .= "DROP VIEW IF EXISTS `$viewName`;\n";
			$output .= $createView . ";\n\n";
		}

		// ===== 3) Respaldar Funciones =====
		$output .= "\n\n-- --------------------------------------------------------\n";
		$output .= "-- Funciones\n";
		$output .= "-- --------------------------------------------------------\n\n";

		$funcResult = $conn->query("SHOW FUNCTION STATUS WHERE Db = DATABASE()");
		while ($row = $funcResult->fetch(PDO::FETCH_ASSOC))
		{
			$functionName    = $row['Name'];
			$stmtFunction    = $conn->query("SHOW CREATE FUNCTION `$functionName`");
			$functionCreate  = $stmtFunction->fetch(PDO::FETCH_ASSOC);
			$createFunction  = removeDefiner($functionCreate['Create Function']);

			$output .= "\n-- Estructura para la función `$functionName`\n";
			$output .= "DROP FUNCTION IF EXISTS `$functionName`;\n";
			$output .= "DELIMITER //\n";
			$output .= $createFunction . "//\n";
			$output .= "DELIMITER ;\n\n";
		}

		// ===== 4) Respaldar Procedimientos =====
		$output .= "\n\n-- --------------------------------------------------------\n";
		$output .= "-- Procedimientos\n";
		$output .= "-- --------------------------------------------------------\n\n";

		$procResult = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE()");
		while ($row = $procResult->fetch(PDO::FETCH_ASSOC))
		{
			$procedureName     = $row['Name'];
			$stmtProcedure     = $conn->query("SHOW CREATE PROCEDURE `$procedureName`");
			$procedureCreate   = $stmtProcedure->fetch(PDO::FETCH_ASSOC);
			$createProcedure   = removeDefiner($procedureCreate['Create Procedure']);

			$output .= "\n-- Estructura para el procedimiento `$procedureName`\n";
			$output .= "DROP PROCEDURE IF EXISTS `$procedureName`;\n";
			$output .= "DELIMITER //\n";
			$output .= $createProcedure . "//\n";
			$output .= "DELIMITER ;\n\n";
		}

		// ===== 5) Respaldar Triggers =====
		$output .= "\n\n-- --------------------------------------------------------\n";
		$output .= "-- Triggers\n";
		$output .= "-- --------------------------------------------------------\n\n";

		foreach ($tables as $table)
		{
			$triggerResult = $conn->query("SHOW TRIGGERS LIKE '$table'");
			if ($triggerResult->rowCount() > 0)
			{
				while ($row = $triggerResult->fetch(PDO::FETCH_ASSOC))
				{
					$triggerName     = $row['Trigger'];
					$stmtTrigger     = $conn->query("SHOW CREATE TRIGGER `$triggerName`");
					$triggerCreate   = $stmtTrigger->fetch(PDO::FETCH_ASSOC);
					// En MySQL 5.7+, el campo se llama 'SQL Original Statement' o similar
					$rawStmt         = isset($triggerCreate['SQL Original Statement'])
						? $triggerCreate['SQL Original Statement']
						: (isset($triggerCreate['Create Trigger'])
							? $triggerCreate['Create Trigger']
							: '');
					$createTrigger   = removeDefiner($rawStmt);

					$output .= "\n-- Estructura para el trigger `$triggerName`\n";
					$output .= "DROP TRIGGER IF EXISTS `$triggerName`;\n";
					$output .= "DELIMITER //\n";
					$output .= $createTrigger . "//\n";
					$output .= "DELIMITER ;\n\n";
				}
			}
		}

		// ===== 6) Respaldar Eventos (si el scheduler está ON) =====
		$eventSchedulerStatus = $conn->query("SELECT @@event_scheduler")->fetch(PDO::FETCH_NUM)[0];
		if ($eventSchedulerStatus == 'ON')
		{
			$output .= "\n\n-- --------------------------------------------------------\n";
			$output .= "-- Eventos\n";
			$output .= "-- --------------------------------------------------------\n\n";

			$eventResult = $conn->query("SHOW EVENTS");
			while ($row = $eventResult->fetch(PDO::FETCH_ASSOC))
			{
				$eventName    = $row['Name'];
				$stmtEvent    = $conn->query("SHOW CREATE EVENT `$eventName`");
				$eventCreate  = $stmtEvent->fetch(PDO::FETCH_ASSOC);
				$createEvent  = removeDefiner($eventCreate['Create Event']);

				$output .= "\n-- Estructura para el evento `$eventName`\n";
				$output .= "DROP EVENT IF EXISTS `$eventName`;\n";
				$output .= "DELIMITER //\n";
				$output .= $createEvent . "//\n";
				$output .= "DELIMITER ;\n\n";
			}
		}

		// Restaurar FOREIGN_KEY_CHECKS
		$output .= "SET FOREIGN_KEY_CHECKS=1;\n";

		// Guardar todo en el archivo
		if (file_put_contents($filename, $output) !== false)
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
