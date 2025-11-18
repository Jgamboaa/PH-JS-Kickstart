<?php

class CompanyController
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function updateCompany($data)
    {
        try
        {
            // Actualizar el registro de la empresa (siempre ID=1) usando PDO
            $stmt = $this->pdo->prepare('
                UPDATE company_data
                SET company_name       = :company_name,
                    company_name_short = :company_name_short,
                    app_name           = :app_name,
                    app_version        = :app_version,
                    developer_name     = :developer_name
                WHERE id = 1
            ');

            $stmt->execute([
                ':company_name'       => $data['company_name'],
                ':company_name_short' => $data['company_name_short'],
                ':app_name'           => $data['app_name'],
                ':app_version'        => $data['app_version'],
                ':developer_name'     => $data['developer_name'],
            ]);

            if ($stmt->rowCount() > 0)
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
            // Cargar el registro de la empresa (siempre ID=1) usando PDO
            $stmt = $this->pdo->prepare('SELECT * FROM company_data WHERE id = 1 LIMIT 1');
            $stmt->execute();
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si no existe, devolver un error
            if (!$company)
            {
                return [
                    'status' => 'error',
                    'message' => 'No se encontró información de la empresa'
                ];
            }

            // Devolver la información de la empresa como array asociativo
            return $company;
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
