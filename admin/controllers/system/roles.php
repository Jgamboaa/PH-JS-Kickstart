<?php
class RoleController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createRole($data)
    {
        try
        {
            $nombre = $data['nombre'];

            $sql = "INSERT INTO roles (nombre) VALUES (:nombre)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Rol añadido'];
            }
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateRole($data)
    {
        try
        {
            $id = $data['id'];
            $nombre = $data['nombre'];

            $sql = "UPDATE roles SET nombre = :nombre WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Rol actualizado'];
            }
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getRole($id)
    {
        try
        {
            $sql = "SELECT * FROM roles WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllRoles()
    {
        try
        {
            $sql = "SELECT * FROM roles";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $data = [];
            while ($row = $stmt->fetch())
            {
                $data[] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre'],
                    'actions' => '<button class="btn btn-success btn-sm edit-btn" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>'
                ];
            }

            return ['data' => $data];
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
