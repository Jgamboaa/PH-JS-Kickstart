<?php
// Importar RedBeanPHP
use RedBeanPHP\R as R;

class CrudCrudController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function CreateCrud($data)
    {
        try
        {
            $client = R::dispense('clients');
            $client->name = $data['name'];
            $client->email = $data['email'];
            $client->phone = $data['phone'] ?? null;

            $id = R::store($client);
            if ($id)
            {
                return [
                    'status' => true,
                    'message' => 'Cliente añadido con éxito',
                ];
            }
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => 'Error al añadir cliente: ' . $e->getMessage()];
        }
    }

    public function UpdateCrud($data)
    {
        try
        {
            $client = R::load('clients', $data['id']);
            if (!$client->id)
            {
                return ['status' => false, 'message' => 'Cliente no encontrado'];
            }

            $client->name = $data['name'];
            $client->email = $data['email'];
            $client->phone = $data['phone'] ?? null;

            R::store($client);
            return ['status' => true, 'message' => 'Cliente actualizado con éxito'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => 'Error al actualizar cliente: ' . $e->getMessage()];
        }
    }

    public function getCrud($id)
    {
        try
        {
            $client = R::load('clients', $id);
            if (!$client->id)
            {
                return ['status' => false, 'message' => 'Cliente no encontrado'];
            }

            return [
                'status' => true,
                'data' => $client,
            ];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => 'Error al obtener cliente: ' . $e->getMessage()];
        }
    }

    public function getAllCruds()
    {
        try
        {
            $data = []; // Inicializar el array de datos
            $clients = R::findAll('clients');
            foreach ($clients as $client)
            {
                $data[] = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'created_at' => date('d/m/Y H-i-s', strtotime($client->created_at)),
                    'updated_at' => date('d/m/Y H-i-s', strtotime($client->updated_at)),
                    'actions' => '<button class="btn btn-success btn-sm edit-btn" data-id="' . $client->id . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>
                                  <button class="btn btn-danger btn-sm delete-btn" data-id="' . $client->id . '"><i class="fa-duotone fa-solid fa-trash fa-lg"></i></button>'
                ];
            }

            return $data; // Devolver el array de datos
        }
        catch (Exception $e)
        {
            return []; // Devolver un array vacío en caso de error
        }
    }

    public function DeleteCrud($id)
    {
        try
        {
            $client = R::load('clients', $id);
            if (!$client->id)
            {
                return ['status' => false, 'message' => 'Cliente no encontrado'];
            }

            R::trash($client);
            return ['status' => true, 'message' => 'Cliente eliminado con éxito'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => 'Error al eliminar cliente: ' . $e->getMessage()];
        }
    }
}
