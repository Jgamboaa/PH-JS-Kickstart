<?php

/**
 * Funciones para gestionar sesiones activas
 */

/**
 * Obtiene todas las sesiones activas de un usuario
 * @param int $user_id ID del usuario
 * @return array Lista de sesiones activas
 */
function getUserActiveSessions($user_id)
{
    global $conn;

    $sql = "SELECT * FROM active_sessions WHERE user_id = ? ORDER BY last_activity DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Finaliza una sesión específica
 * @param int $session_id ID de la sesión a finalizar
 * @param int $user_id ID del usuario (para verificación de seguridad)
 * @return bool Resultado de la operación
 */
function terminateSession($session_id, $user_id)
{
    global $conn;

    $sql = "DELETE FROM active_sessions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$session_id, $user_id]);

    // Registrar esta acción en los logs de sesión
    if ($result)
    {
        $sql = "INSERT INTO session_logs (user_id, log_type, old_value, new_value, created_at) 
                VALUES (?, 'session_terminated', ?, NULL, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, "Session ID: $session_id"]);
    }

    return $result;
}

/**
 * Finaliza todas las sesiones excepto la actual
 * @param int $user_id ID del usuario
 * @param string $current_device_token Token del dispositivo actual
 * @return int Número de sesiones finalizadas
 */
function terminateOtherSessions($user_id, $current_device_token)
{
    global $conn;

    $sql = "DELETE FROM active_sessions WHERE user_id = ? AND device_token != ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $current_device_token]);

    $count = $stmt->rowCount();

    // Registrar esta acción en los logs de sesión
    if ($count > 0)
    {
        $sql = "INSERT INTO session_logs (user_id, log_type, old_value, new_value, created_at) 
                VALUES (?, 'all_sessions_terminated', ?, NULL, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id, "Terminated $count sessions"]);
    }

    return $count;
}

/**
 * Limpia sesiones inactivas más antiguas que cierto período
 * @param int $hours Número de horas de inactividad
 * @return int Número de sesiones eliminadas
 */
function cleanupInactiveSessions($hours = 48)
{
    global $conn;

    $sql = "DELETE FROM active_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? HOUR)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hours]);

    return $stmt->rowCount();
}
