<?php
// filepath: c:\laragon\www\PH-JS-Kickstart\admin\includes\system\security.php
include '../session.php';
require_once dirname(__DIR__) . '/functions/2fa_functions.php';

// Verificar si el usuario tiene permisos de administrador
$admin_id = $user['id'];
$roles_ids = explode(',', $user['roles_ids']);

if (!in_array(1, $roles_ids))
{
    echo json_encode(['status' => false, 'message' => 'No tienes permiso para realizar esta acción']);
    exit;
}

// Manejar solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']))
{
    $action = $_GET['action'];

    switch ($action)
    {
        case 'get_users_2fa_status':
            getUsersTfaStatus();
            break;

        case 'get_security_settings':
            getSecuritySettings();
            break;

        default:
            echo json_encode(['status' => false, 'message' => 'Acción no reconocida']);
            break;
    }
}

// Manejar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']))
{
    $action = $_POST['action'];

    switch ($action)
    {
        case 'save_security_settings':
            saveSecuritySettings($_POST['settings']);
            break;

        default:
            echo json_encode(['status' => false, 'message' => 'Acción no reconocida']);
            break;
    }
}

/**
 * Obtiene el estado de 2FA de todos los usuarios
 */
function getUsersTfaStatus()
{
    global $conn;

    $sql = "SELECT id, username, user_firstname, user_lastname, tfa_enabled FROM admin";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $users = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        $users[] = [
            'username' => $row['username'],
            'nombre' => $row['user_firstname'] . ' ' . $row['user_lastname'],
            'tfa_enabled' => $row['tfa_enabled']
        ];
    }

    echo json_encode(['data' => $users]);
}

/**
 * Obtiene la configuración de seguridad actual
 */
function getSecuritySettings()
{
    global $conn;

    // Por ahora, estos valores son ficticios ya que no tenemos una tabla de configuración
    // En una implementación real, deberías leer estos valores de una tabla de configuración
    $settings = [
        'enforce_admin_2fa' => 0,
        'login_attempts' => 3,
        'session_time' => 30,
        'lock_duration' => 15
    ];

    echo json_encode(['status' => true, 'data' => $settings]);
}

/**
 * Guarda la configuración de seguridad
 */
function saveSecuritySettings($settings)
{
    global $conn;

    // Validar datos recibidos
    if (
        !isset($settings['enforce_admin_2fa']) ||
        !isset($settings['login_attempts']) ||
        !isset($settings['session_time']) ||
        !isset($settings['lock_duration'])
    )
    {
        echo json_encode(['status' => false, 'message' => 'Datos de configuración incompletos']);
        return;
    }

    // En una implementación real, guardarías estos valores en una tabla de configuración
    // Por ahora, solo devolvemos un mensaje de éxito

    echo json_encode([
        'status' => true,
        'message' => 'Configuración guardada correctamente'
    ]);
}
