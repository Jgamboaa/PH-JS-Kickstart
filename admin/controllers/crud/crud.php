<?php
// Importar RedBeanPHP
use RedBeanPHP\R as R;

/**
 * Controlador CRUD para la gestión de clientes
 * 
 * Este controlador implementa las operaciones básicas de un CRUD:
 * - Create: Crear nuevos registros
 * - Read: Leer registros existentes
 * - Update: Actualizar registros
 * - Delete: Eliminar registros
 * 
 * Utiliza RedBeanPHP como ORM para interactuar con la base de datos.
 */
class CrudCrudController
{
    /** @var object Conexión a la base de datos */
    private $conn;

    /**
     * Constructor del controlador
     * 
     * @param object $conn Conexión a la base de datos
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Crea un nuevo cliente en la base de datos
     * 
     * @param array $data Datos del cliente a crear
     * @return array Resultado de la operación
     */
    public function CreateCrud($data)
    {
        try
        {
            // [EXPLICACIÓN] RedBeanPHP utiliza el método dispense para crear un nuevo bean (objeto)
            // que representa una fila en la tabla 'clients'
            $client = R::dispense('clients');

            // [EXPLICACIÓN] Asignamos los valores recibidos a las propiedades del bean
            $client->name = $data['name'];
            $client->email = $data['email'];
            $client->phone = $data['phone'] ?? null; // Operador de fusión null (PHP 7+)

            // [EXPLICACIÓN] Campos de auditoría - fechas de creación y actualización
            $client->created_at = date('Y-m-d H:i:s');
            $client->updated_at = date('Y-m-d H:i:s');

            // [EXPLICACIÓN] R::store guarda el bean en la base de datos y devuelve el ID
            $id = R::store($client);

            if ($id)
            {
                return [
                    'status' => true,
                    'message' => 'Cliente añadido con éxito',
                    'id' => $id,
                    'operation' => 'CREATE',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
            else
            {
                throw new Exception('Error al guardar el cliente');
            }
        }
        catch (Exception $e)
        {
            // [EXPLICACIÓN] En caso de error, devolvemos un mensaje informativo
            return [
                'status' => false,
                'message' => 'Error al añadir cliente: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'operation' => 'CREATE',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Actualiza los datos de un cliente existente
     * 
     * @param array $data Datos actualizados del cliente
     * @return array Resultado de la operación
     */
    public function UpdateCrud($data)
    {
        try
        {
            // [EXPLICACIÓN] R::load carga un bean existente por su ID
            $client = R::load('clients', $data['id']);

            // [EXPLICACIÓN] Verificamos que el cliente exista
            if (!$client->id)
            {
                return [
                    'status' => false,
                    'message' => 'Cliente no encontrado',
                    'operation' => 'UPDATE',
                    'id' => $data['id']
                ];
            }

            // [EXPLICACIÓN] Actualizamos las propiedades del bean
            $client->name = $data['name'];
            $client->email = $data['email'];
            $client->phone = $data['phone'] ?? null;

            // [EXPLICACIÓN] Solo actualizamos la fecha de modificación
            $client->updated_at = date('Y-m-d H:i:s');

            // [EXPLICACIÓN] Guardamos los cambios
            R::store($client);

            return [
                'status' => true,
                'message' => 'Cliente actualizado con éxito',
                'operation' => 'UPDATE',
                'id' => $client->id,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        catch (Exception $e)
        {
            return [
                'status' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'operation' => 'UPDATE',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Obtiene los datos de un cliente específico
     * 
     * @param int $id ID del cliente a obtener
     * @return array Datos del cliente o mensaje de error
     */
    public function getCrud($id)
    {
        try
        {
            // [EXPLICACIÓN] Cargamos el cliente por su ID
            $client = R::load('clients', $id);

            // [EXPLICACIÓN] Verificamos que el cliente exista
            if (!$client->id)
            {
                return [
                    'status' => false,
                    'message' => 'Cliente no encontrado',
                    'operation' => 'READ',
                    'id' => $id
                ];
            }

            // [EXPLICACIÓN] Devolvemos los datos del cliente
            return [
                'status' => true,
                'data' => $client->export(), // Convertimos el bean a un array asociativo
                'operation' => 'READ',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        catch (Exception $e)
        {
            return [
                'status' => false,
                'message' => 'Error al obtener cliente: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'operation' => 'READ',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Obtiene todos los clientes de la base de datos
     * 
     * @return array Lista de clientes con formato para DataTables
     */
    public function getAllCruds()
    {
        try
        {
            $data = []; // Inicializar el array de datos

            // [EXPLICACIÓN] R::findAll obtiene todos los registros de una tabla
            $clients = R::findAll('clients', ' ORDER BY id DESC');

            foreach ($clients as $client)
            {
                // [EXPLICACIÓN] Formateamos cada cliente para la tabla
                $data[] = [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone ?: '<span class="text-muted">No disponible</span>',
                    'created_at' => date('d/m/Y H:i:s', strtotime($client->created_at)),
                    'updated_at' => date('d/m/Y H:i:s', strtotime($client->updated_at)),
                    // [EXPLICACIÓN] Botones de acción con clases de Bootstrap
                    'actions' => '
                        <div class="btn-group">
                            <button class="btn btn-success btn-sm edit-btn" data-id="' . $client->id . '">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="' . $client->id . '">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <button class="btn btn-info btn-sm info-btn" data-id="' . $client->id . '">
                                <i class="fa-solid fa-info"></i>
                            </button>
                        </div>'
                ];
            }

            return $data; // Devolver el array de datos
        }
        catch (Exception $e)
        {
            // [EXPLICACIÓN] En caso de error, registramos en el log y devolvemos array vacío
            error_log('Error en getAllCruds: ' . $e->getMessage());
            return []; // Devolver un array vacío en caso de error
        }
    }

    /**
     * Elimina un cliente de la base de datos
     * 
     * @param int $id ID del cliente a eliminar
     * @return array Resultado de la operación
     */
    public function DeleteCrud($id)
    {
        try
        {
            // [EXPLICACIÓN] Cargamos el cliente por su ID
            $client = R::load('clients', $id);

            // [EXPLICACIÓN] Verificamos que el cliente exista
            if (!$client->id)
            {
                return [
                    'status' => false,
                    'message' => 'Cliente no encontrado',
                    'operation' => 'DELETE',
                    'id' => $id
                ];
            }

            // [EXPLICACIÓN] Guardamos información antes de eliminar (para el mensaje)
            $clientInfo = [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email
            ];

            // [EXPLICACIÓN] R::trash elimina el bean de la base de datos
            R::trash($client);

            return [
                'status' => true,
                'message' => 'Cliente eliminado con éxito',
                'client_info' => $clientInfo,
                'operation' => 'DELETE',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        catch (Exception $e)
        {
            return [
                'status' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'operation' => 'DELETE',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
}
