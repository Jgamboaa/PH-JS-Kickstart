<?php

/**
 * Punto de entrada para las operaciones CRUD
 * 
 * Este archivo actúa como intermediario entre la interfaz de usuario
 * y el controlador, gestionando las solicitudes AJAX y devolviendo
 * respuestas en formato JSON.
 */

// Incluir archivos necesarios
include '../session.php';
include '../../controllers/crud/crud.php';

// Inicializar el controlador
$crudController = new CrudCrudController($conn);

// Variable para rastreo de operaciones (para fines didácticos)
$operationInfo = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
];

// Procesar solicitudes POST (create, update, get, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Determinar la operación CRUD solicitada
    $crud = $_POST['crud'];
    $operationInfo['operation'] = $crud;

    // Log de la operación (solo con fines didácticos)
    $logEntry = "Operation: $crud | Time: " . $operationInfo['timestamp'] . " | IP: " . $operationInfo['ip'] . "\n";
    error_log($logEntry);

    // Procesar según la operación solicitada
    switch ($crud)
    {
        case 'create':
            // [EXPLICACIÓN] Llamamos al método CreateCrud del controlador
            $result = $crudController->CreateCrud($_POST);

            // [EXPLICACIÓN] Añadimos información adicional a la respuesta
            $result['request_info'] = $operationInfo;

            // [EXPLICACIÓN] Devolvemos la respuesta en formato JSON
            echo json_encode($result);
            break;

        case 'edit':
            // [EXPLICACIÓN] Llamamos al método UpdateCrud del controlador
            $result = $crudController->UpdateCrud($_POST);
            $result['request_info'] = $operationInfo;
            echo json_encode($result);
            break;

        case 'get':
            // [EXPLICACIÓN] Llamamos al método getCrud del controlador
            $result = $crudController->getCrud($_POST['id']);
            $result['request_info'] = $operationInfo;
            echo json_encode($result);
            break;

        case 'delete':
            // [EXPLICACIÓN] Llamamos al método DeleteCrud del controlador
            $result = $crudController->DeleteCrud($_POST['id']);
            $result['request_info'] = $operationInfo;
            echo json_encode($result);
            break;

        default:
            // [EXPLICACIÓN] Operación no reconocida
            echo json_encode([
                'status' => false,
                'message' => 'Operación no válida',
                'request_info' => $operationInfo
            ]);
    }
}
// Procesar solicitudes GET (fetch)
elseif (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
    // [EXPLICACIÓN] Llamamos al método getAllCruds del controlador
    $operationInfo['operation'] = 'fetch';
    $result = $crudController->getAllCruds();

    // [EXPLICACIÓN] Formato esperado por DataTables
    $response = [
        'data' => $result,
        'request_info' => $operationInfo,
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($result),
        'recordsFiltered' => count($result)
    ];

    echo json_encode($response);
}
else
{
    // [EXPLICACIÓN] Solicitud no válida
    $operationInfo['operation'] = 'invalid';
    echo json_encode([
        'status' => false,
        'message' => 'Solicitud no válida',
        'request_info' => $operationInfo
    ]);
}
