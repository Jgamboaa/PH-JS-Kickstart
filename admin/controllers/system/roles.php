<?php

class RoleController
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createRole($data)
    {
        try
        {
            // Insertar nuevo rol usando PDO
            $stmt = $this->pdo->prepare('INSERT INTO roles (nombre) VALUES (:nombre)');
            $stmt->execute([
                ':nombre' => $data['nombre'],
            ]);

            $id = $this->pdo->lastInsertId();

            if ($id)
            {
                return ['status' => true, 'message' => 'Rol añadido'];
            }
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateRole($data)
    {
        try
        {
            // Verificar que el rol existe
            $stmt = $this->pdo->prepare('SELECT id FROM roles WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $data['id']]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$role)
            {
                return ['status' => false, 'message' => 'Rol no encontrado'];
            }

            // Actualizar propiedades
            $stmtUpdate = $this->pdo->prepare('UPDATE roles SET nombre = :nombre WHERE id = :id');
            $stmtUpdate->execute([
                ':nombre' => $data['nombre'],
                ':id'     => $data['id'],
            ]);

            return ['status' => true, 'message' => 'Rol actualizado'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getRole($id)
    {
        try
        {
            // Cargar el rol por ID usando PDO
            $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$role)
            {
                return null;
            }

            return $role;
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllRoles()
    {
        try
        {
            // Obtener todos los roles usando PDO
            $stmt = $this->pdo->prepare('SELECT * FROM roles');
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($roles as $role)
            {
                $data[] = [
                    'id'      => $role['id'],
                    'nombre'  => $role['nombre'],
                    'actions' => '<button class="btn btn-success btn-sm edit-btn" data-id="' . $role['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>'
                ];
            }

            return ['data' => $data];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
