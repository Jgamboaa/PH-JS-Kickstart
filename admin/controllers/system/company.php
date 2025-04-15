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
            // Preparar la consulta SQL
            $sql = "UPDATE company_data SET
                rep_name = :rep_name,
                company_name = :company_name,
                company_name_short = :company_name_short,
                address = :address,
                rep_age_ltr = :rep_age_ltr,
                rep_marital_status = :rep_marital_status,
                rep_nacionality = :rep_nacionality,
                rep_studies = :rep_studies,
                rep_dpi_number = :rep_dpi_number,
                rep_position = :rep_position,
                company_nit = :company_nit,
                company_employers_number = :company_employers_number,
                rep_contract = :rep_contract,
                app_name = :app_name,
                app_version = :app_version,
                developer_name = :developer_name
                WHERE id = 1";

            $stmt = $this->conn->prepare($sql);

            // Vincular parámetros
            $stmt->bindParam(':rep_name', $data['rep_name']);
            $stmt->bindParam(':company_name', $data['company_name']);
            $stmt->bindParam(':company_name_short', $data['company_name_short']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':rep_age_ltr', $data['rep_age_ltr']);
            $stmt->bindParam(':rep_marital_status', $data['rep_marital_status']);
            $stmt->bindParam(':rep_nacionality', $data['rep_nacionality']);
            $stmt->bindParam(':rep_studies', $data['rep_studies']);
            $stmt->bindParam(':rep_dpi_number', $data['rep_dpi_number']);
            $stmt->bindParam(':rep_position', $data['rep_position']);
            $stmt->bindParam(':company_nit', $data['company_nit']);
            $stmt->bindParam(':company_employers_number', $data['company_employers_number']);
            $stmt->bindParam(':rep_contract', $data['rep_contract']);
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
