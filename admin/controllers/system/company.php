<?php
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
            // Preparar la consulta SQL con solo los campos existentes en la nueva estructura
            $sql = "UPDATE company_data SET
                company_name = :company_name,
                company_name_short = :company_name_short,
                app_name = :app_name,
                app_version = :app_version,
                developer_name = :developer_name
                WHERE id = 1";

            $stmt = $this->conn->prepare($sql);

            // Vincular parámetros - solo los campos que existen en la nueva estructura
            $stmt->bindParam(':company_name', $data['company_name']);
            $stmt->bindParam(':company_name_short', $data['company_name_short']);
            $stmt->bindParam(':app_name', $data['app_name']);
            $stmt->bindParam(':app_version', $data['app_version']);
            $stmt->bindParam(':developer_name', $data['developer_name']);

            // Ejecutar la consulta
            if ($stmt->execute())
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
        catch (PDOException $e)
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
            $sql = "SELECT * FROM company_data WHERE id = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetch();
        }
        catch (PDOException $e)
        {
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
