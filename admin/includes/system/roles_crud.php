<?php
include '../session.php';
include '../../controllers/system/roles.php';

$roleController = new RoleController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $crud = $_POST['crud'];

    switch ($crud)
    {
        case 'create':
            $result = $roleController->createRole($_POST);
            echo json_encode($result);
            break;

        case 'edit':
            $result = $roleController->updateRole($_POST);
            echo json_encode($result);
            break;

        case 'get':
            $result = $roleController->getRole($_POST['id']);
            echo json_encode($result);
            break;
    }
}
elseif (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
    $result = $roleController->getAllRoles();
    echo json_encode($result);
}
