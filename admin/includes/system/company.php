<?php
include '../session.php';
include '../../controllers/system/company.php';

// Usar la conexión PDO global
global $pdo;
$companyController = new CompanyController($pdo);
$response = array();

// Si es una petición GET, devolvemos la información de la empresa
if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $response = $companyController->getCompanyInfo();
}
// Si es POST y viene el nombre de la empresa, actualizamos
elseif (isset($_POST['company_name']))
{
    // Usar el controlador para actualizar la información de la empresa
    $response = $companyController->updateCompany($_POST);
}
else
{
    $response['status'] = 'error';
    $response['message'] = 'Complete el formulario de edición primero';
}

echo json_encode($response);
