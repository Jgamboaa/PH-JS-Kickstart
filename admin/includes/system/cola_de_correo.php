<?php
include __DIR__ . '/../mail_server.php';
include __DIR__ . '/../conn.php';

//aplica zona horaria America/Guatemala a la BD y a php

date_default_timezone_set('America/Guatemala');
$conn->query("SET time_zone = '-06:00'");

// Obtener correos pendientes
$sql = "SELECT * FROM cola_correos WHERE estado = 0";
$query = $conn->query($sql);

while ($row = $query->fetch_assoc()) {
    // Convertir destinatarios string a array
    $destinatarios = explode('/', $row['destinatarios']);

    // Convertir archivos string a array si existen
    $archivos = !empty($row['archivos']) ? explode('/', $row['archivos']) : [];

    // Intentar enviar el correo
    $resultado = enviarCorreo(
        $row['asunto'],
        $row['contenido'],
        $destinatarios,
        $archivos
    );

    // Preparar la actualización según el resultado
    $nuevo_estado = $resultado ? 1 : 2;
    $timestamp = date('Y-m-d H:i:s');
    $error = isset($_SESSION['error']) ? $_SESSION['error'] : NULL;

    // Actualizar el registro en la base de datos
    $stmt = $conn->prepare("UPDATE cola_correos SET 
        estado = ?,
        enviado_en = ?,
        error = ?
        WHERE id_cola = ?");

    $stmt->bind_param(
        "issi",
        $nuevo_estado,
        $timestamp,
        $error,
        $row['id_cola']
    );

    $stmt->execute();
    $stmt->close();

    // Limpiar el error de sesión si existe
    if (isset($_SESSION['error'])) {
        unset($_SESSION['error']);
    }

    echo "Correo enviado a: " . implode(', ', $destinatarios) . "<br>";
}

$conn->close();
