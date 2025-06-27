<?php
// Importar RedBeanPHP
use RedBeanPHP\R as R;

class RoleController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        // RedBeanPHP ya está configurado en db_conn.php, así que no es necesario inicializarlo aquí
    }

    public function createRole($data)
    {
        try
        {
            // Crear un bean de rol
            $role = R::dispense('roles');
            $role->nombre = $data['nombre'];

            // Guardar el bean y obtener el ID
            $id = R::store($role);

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
            // Cargar el rol existente
            $role = R::load('roles', $data['id']);

            if (!$role->id)
            {
                return ['status' => false, 'message' => 'Rol no encontrado'];
            }

            // Actualizar propiedades
            $role->nombre = $data['nombre'];

            // Guardar cambios
            R::store($role);

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
            // Cargar el rol por ID
            $role = R::load('roles', $id);

            if (!$role->id)
            {
                return null;
            }

            // Convertir el bean a un array asociativo
            return $role->export();
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
            // Obtener todos los roles
            $roles = R::findAll('roles');

            $data = [];
            foreach ($roles as $role)
            {
                $data[] = [
                    'id' => $role->id,
                    'nombre' => $role->nombre,
                    'actions' => '<button class="btn btn-success btn-sm edit-btn" data-id="' . $role->id . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>'
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
