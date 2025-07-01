<?php
include '../session.php';
include '../../controllers/crud/crud.php';

$crudController = new CrudCrudController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $crud = $_POST['crud'];

    switch ($crud)
    {
        case 'create':
            $result = $crudController->CreateCrud($_POST);
            echo json_encode($result);
            break;

        case 'edit':
            $result = $crudController->UpdateCrud($_POST);
            echo json_encode($result);
            break;

        case 'get':
            $result = $crudController->getCrud($_POST['id']);
            echo json_encode($result);
            break;

        case 'delete':
            $result = $crudController->DeleteCrud($_POST['id']);
            echo json_encode($result);
            break;
    }
}
elseif (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
    $result = $crudController->getAllCruds();
    echo json_encode(['data' => $result]);
}
