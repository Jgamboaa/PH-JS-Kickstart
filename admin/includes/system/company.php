<?php
include '../session.php';
include '../../controllers/system/company.php';

$companyController = new CompanyController($conn);
$response = array();

if (isset($_POST['company_name']))
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
