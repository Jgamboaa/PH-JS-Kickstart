<?php
include '../session.php';

$response = array();

if (isset($_POST['company_name']))
{
    $rep_name = $_POST['rep_name'];
    $company_name = $_POST['company_name'];
    $company_name_short = $_POST['company_name_short'];
    $address = $_POST['address'];
    $rep_age_ltr = $_POST['rep_age_ltr'];
    $rep_marital_status = $_POST['rep_marital_status'];
    $rep_nacionality = $_POST['rep_nacionality'];
    $rep_studies = $_POST['rep_studies'];
    $rep_dpi_number = $_POST['rep_dpi_number'];
    $rep_position = $_POST['rep_position'];
    $company_nit = $_POST['company_nit'];
    $company_employers_number = $_POST['company_employers_number'];
    $rep_contract = $_POST['rep_contract'];
    $app_name = $_POST['app_name'];
    $app_version = $_POST['app_version'];
    $developer_name = $_POST['developer_name'];

    try
    {
        // Actualizar la tabla company_data
        $sql = "UPDATE company_data SET
            rep_name = ?,
            company_name = ?,
            company_name_short = ?,
            address = ?,
            rep_age_ltr = ?,
            rep_marital_status = ?,
            rep_nacionality = ?,
            rep_studies = ?,
            rep_dpi_number = ?,
            rep_position = ?,
            company_nit = ?,
            company_employers_number = ?,
            rep_contract = ?,
            app_name = ?,
            app_version = ?,
            developer_name = ?
            WHERE id = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssssssssss",
            $rep_name,
            $company_name,
            $company_name_short,
            $address,
            $rep_age_ltr,
            $rep_marital_status,
            $rep_nacionality,
            $rep_studies,
            $rep_dpi_number,
            $rep_position,
            $company_nit,
            $company_employers_number,
            $rep_contract,
            $app_name,
            $app_version,
            $developer_name
        );
        if ($stmt->execute())
        {
            $response['status'] = 'success';
            $response['message'] = 'Información de la empresa actualizada correctamente';
        }
        else
        {
            throw new Exception('Error al actualizar la base de datos');
        }
    }
    catch (Exception $e)
    {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}
else
{
    $response['status'] = 'error';
    $response['message'] = 'Complete el formulario de edición primero';
}

echo json_encode($response);
