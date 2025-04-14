<?php
include_once '../session.php';

function addToQueue($destinatarios, $asunto, $contenido, $archivos = null)
{
    global $conn;

    // Obtener el ID del usuario de la sesión
    $creator_id = isset($_SESSION['admin']) ? $_SESSION['admin'] : 0;

    // Formatear datos
    $destinatarios_str = is_array($destinatarios) ? implode(',', $destinatarios) : $destinatarios;
    $archivos_str = is_array($archivos) ? implode(',', $archivos) : $archivos;
    $fecha_actual = date('Y-m-d H:i:s');

    // Escapar valores para prevenir SQL injection
    $destinatarios_str = $conn->real_escape_string($destinatarios_str);
    $asunto = $conn->real_escape_string($asunto);
    $contenido = $conn->real_escape_string($contenido);
    $archivos_str = $archivos_str ? $conn->real_escape_string($archivos_str) : null;

    // Preparar la consulta SQL
    $sql = "INSERT INTO cola_correos (destinatarios, asunto, contenido, archivos, estado, creado_en, creador) 
            VALUES ('$destinatarios_str', '$asunto', '$contenido', " .
        ($archivos_str ? "'$archivos_str'" : "NULL") .
        ", 0, '$fecha_actual', $creator_id)";

    // Ejecutar la consulta
    if ($conn->query($sql))
    {
        return ['status' => true, 'message' => 'Correo añadido a la cola con éxito'];
    }
    else
    {
        return ['status' => false, 'message' => 'Error al añadir el correo a la cola: ' . $conn->error];
    }
}
