<?php
// Importar RedBeanPHP
use RedBeanPHP\R as R;

class CompanyController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function updateCompany($data)
    {
        try
        {
            // Cargar el registro de la empresa (siempre ID=1)
            $company = R::load('company_data', 1);

            // Asignar los valores desde los datos recibidos
            $company->company_name = $data['company_name'];
            $company->company_name_short = $data['company_name_short'];
            $company->app_name = $data['app_name'];
            $company->app_version = $data['app_version'];
            $company->developer_name = $data['developer_name'];

            // Guardar los cambios
            $id = R::store($company);

            if ($id)
            {
                return [
                    'status' => 'success',
                    'message' => 'Información de la empresa actualizada correctamente'
                ];
            }
            else
            {
                return [
                    'status' => 'error',
                    'message' => 'Error al actualizar la información de la empresa'
                ];
            }
        }
        catch (Exception $e)
        {
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getCompanyInfo()
    {
        try
        {
            // Cargar el registro de la empresa (siempre ID=1)
            $company = R::load('company_data', 1);

            // Si no existe, devolver un error
            if (!$company->id)
            {
                return [
                    'status' => 'error',
                    'message' => 'No se encontró información de la empresa'
                ];
            }

            // Convertir el bean a un array asociativo
            return $company->export();
        }
        catch (Exception $e)
        {
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
