<?php
include '../session.php';
include '../../controllers/system/variables.php';

$configController = new VariablesController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $crud = $_POST['crud'] ?? '';

    switch ($crud)
    {
        case 'create':
        case 'update':
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $value = isset($_POST['value']) ? trim($_POST['value']) : '';

            $result = $configController->saveEnvVariable($name, $value);
            echo json_encode($result);
            break;

        case 'delete':
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $result = $configController->deleteEnvVariable($name);
            echo json_encode($result);
            break;

        default:
            echo json_encode(['status' => false, 'message' => 'Acción no válida']);
            break;
    }
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
    $result = $configController->getEnvVariables();
    echo json_encode($result);
}
