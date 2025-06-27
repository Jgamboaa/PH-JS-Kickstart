<?php
// Importar RedBeanPHP
use RedBeanPHP\R as R;

class UserController
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function createUser($data, $files)
    {
        try
        {
            // Crear un bean de usuario (admin)
            $user = R::dispense('admin');
            $user->username = $data['usuario'];
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->user_firstname = $data['firstname'];
            $user->user_lastname = $data['lastname'];
            $user->admin_gender = $data['gender'];
            $user->roles_ids = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";
            $user->created_on = date("Y-m-d");
            $user->tfa_required = isset($data['tfa_required']) ? (int)$data['tfa_required'] : 0;
            
            // Manejar subida de foto
            $user->photo = $this->handlePhotoUpload($files, $data['usuario']);
            
            // Guardar el usuario
            $id = R::store($user);
            
            if ($id) {
                return ['status' => true, 'message' => 'Usuario agregado correctamente'];
            }
            
            return ['status' => false, 'message' => 'Error al agregar usuario'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateUser($data, $files)
    {
        try
        {
            // Cargar el usuario existente
            $user = R::load('admin', $data['id']);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Verificar si la contraseña ha cambiado
            $user->password = $data['password'] == $user->password ?
                $user->password : password_hash($data['password'], PASSWORD_DEFAULT);
                
            // Actualizar propiedades
            $user->username = $data['usuario'];
            $user->user_firstname = $data['firstname'];
            $user->user_lastname = $data['lastname'];
            $user->admin_gender = $data['gender'];
            $user->roles_ids = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";
            $user->tfa_required = isset($data['tfa_required']) ? (int)$data['tfa_required'] : 0;
            
            // Manejar subida de foto
            $filename = $files['photo']['name'];
            if (!empty($filename)) {
                // Eliminar foto anterior si existe
                if ($user->photo && file_exists('../../../images/admins/' . $user->photo)) {
                    unlink('../../../images/admins/' . $user->photo);
                }
                // Subir nueva foto
                $user->photo = $this->handlePhotoUpload($files, $data['usuario']);
            }
            
            // Guardar cambios
            R::store($user);
            
            return ['status' => true, 'message' => 'Perfil de usuario actualizado correctamente'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateProfile($data, $files, $current_user)
    {
        try
        {
            // Verificar contraseña actual
            if (!password_verify($data['curr_password'], $current_user['password'])) {
                return ['status' => false, 'message' => 'Contraseña actual incorrecta'];
            }
            
            // Cargar el usuario
            $user = R::load('admin', $current_user['id']);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Verificar si la contraseña ha cambiado
            $user->password = $data['password'] == $current_user['password'] ?
                $current_user['password'] : password_hash($data['password'], PASSWORD_DEFAULT);
                
            $user->color_mode = $data['color_mode'];
            
            // Manejar subida de foto
            if (!empty($files['photo']['name'])) {
                // Eliminar foto anterior si existe
                if (file_exists('../../../images/admins/' . $current_user['photo']) && !empty($current_user['photo'])) {
                    unlink('../../../images/admins/' . $current_user['photo']);
                }
                $ext = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'photo_' . $current_user['username'] . '.' . $ext;
                move_uploaded_file($files['photo']['tmp_name'], '../../../images/admins/' . $filename);
                $user->photo = $filename;
            }
            
            // Guardar cambios
            R::store($user);
            
            return ['status' => true, 'message' => 'Perfil actualizado correctamente'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function deleteUser($id, $current_user_id)
    {
        try
        {
            // No permitir que un usuario se elimine a sí mismo
            if ($id == $current_user_id) {
                return ['status' => false, 'message' => 'No puedes eliminar tu propio usuario'];
            }
            
            // Cargar el usuario
            $user = R::load('admin', $id);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Eliminar foto si existe
            if ($user->photo && file_exists('../../../images/admins/' . $user->photo)) {
                unlink('../../../images/admins/' . $user->photo);
            }
            
            // Eliminar usuario
            R::trash($user);
            
            return ['status' => true, 'message' => 'Usuario eliminado correctamente'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getUser($id)
    {
        try
        {
            // Cargar el usuario por ID
            $user = R::load('admin', $id);
            
            if ($user->id) {
                // Convertir el bean a un array asociativo
                return $user->export();
            } else {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAllUsers($current_user_id)
    {
        try
        {
            // Obtener todos los roles para mostrar sus nombres
            $roles_map = $this->getAllRoles();
            
            // Obtener todos los usuarios
            $users = R::findAll('admin');
            
            // Filtrar usuarios si no es el admin principal
            if ($current_user_id != 1) {
                $users = R::find('admin', ' id != 1 ');
            }
            
            $data = [];
            foreach ($users as $user) {
                $photoPath = "../../../images/admins/" . $user->photo;
                $photoSrc = (file_exists($photoPath) && !empty($user->photo)) ?
                    $photoPath : "../../../images/admins/profile.png";
                
                $acciones = '
                    <button class="btn btn-sm btn-success edit" data-id="' . $user->id . '">
                        <i class="fa-duotone fa-solid fa-pen fa-lg"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="' . $user->id . '">
                        <i class="fa-duotone fa-solid fa-trash-xmark fa-lg"></i>
                    </button>
                ';
                
                // Añadir botones para gestionar 2FA
                if ($current_user_id != $user->id) {
                    $acciones .= '
                        <button class="btn btn-sm btn-info reset-2fa" data-id="' . $user->id . '" title="Restablecer 2FA">
                            <i class="fa-duotone fa-solid fa-shield-check fa-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-warning new-backup-codes" data-id="' . $user->id . '" title="Generar códigos de respaldo">
                            <i class="fa-duotone fa-solid fa-key fa-lg"></i>
                        </button>
                    ';
                }
                
                $ultimo_login = !empty($user->last_login) ?
                    date('d/m/Y - h:i:s A', strtotime($user->last_login)) : 'No disponible';
                
                // Procesar roles
                $roles_mostrados = $this->formatUserRoles($user->roles_ids, $roles_map);
                
                // Estado MFA
                $mfa_status = $user->tfa_enabled ?
                    '<span class="badge badge-success">Activado</span>' :
                    '<span class="badge badge-danger">Desactivado</span>';
                
                // MFA Requerido
                $mfa_required = $user->tfa_required ?
                    '<span class="badge badge-primary">Obligatorio</span>' :
                    '<span class="badge badge-secondary">Opcional</span>';
                
                $data[] = [
                    'foto' => '<img src="' . $photoSrc . '" class="img-circle" width="40px" height="40px" loading="lazy">',
                    'nombre' => $user->user_firstname . ' ' . $user->user_lastname,
                    'correo' => $user->username,
                    'roles' => $roles_mostrados,
                    'mfa_status' => $mfa_status,
                    'mfa_required' => $mfa_required,
                    'ultimo_login' => $ultimo_login,
                    'acciones' => $acciones
                ];
            }
            
            return ['data' => $data];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function getAllRoles()
    {
        try
        {
            // Obtener todos los roles usando RedBeanPHP
            $roles = R::findAll('roles');
            $roles_map = [];
            
            foreach ($roles as $role) {
                $roles_map[$role->id] = $role->nombre;
            }
            
            return $roles_map;
        }
        catch (Exception $e)
        {
            return [];
        }
    }

    private function formatUserRoles($roles_ids, $roles_map)
    {
        if (empty($roles_ids))
        {
            return 'Sin roles';
        }

        $roles_ids_array = explode(',', $roles_ids);
        $roles_nombres = [];

        foreach ($roles_ids_array as $role_id)
        {
            if (isset($roles_map[$role_id]))
            {
                $roles_nombres[] = $roles_map[$role_id];
            }
        }

        if (empty($roles_nombres))
        {
            return 'Sin roles';
        }

        $roles_mostrados = '<ul class="mb-0">';
        foreach ($roles_nombres as $nombre)
        {
            $roles_mostrados .= '<li>' . $nombre . '</li>';
        }
        $roles_mostrados .= '</ul>';

        return $roles_mostrados;
    }

    private function handlePhotoUpload($files, $username)
    {
        $filename = $files['photo']['name'];
        if (empty($filename))
        {
            return '';
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_filename = 'user_' . $username . '_' . time() . '.' . $ext;
        move_uploaded_file($files['photo']['tmp_name'], '../../../images/admins/' . $new_filename);

        return $new_filename;
    }

    // Nuevos métodos para gestionar 2FA

    /**
     * Actualiza si el 2FA es requerido para un usuario
     * @param int $userId ID del usuario
     * @param bool $required Si el 2FA es requerido (1) o no (0)
     * @return array Resultado de la operación
     */
    public function updateMFARequired($userId, $required)
    {
        try
        {
            // Cargar el usuario
            $user = R::load('admin', $userId);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Actualizar el estado de 2FA requerido
            $user->tfa_required = (int)$required;
            R::store($user);
            
            return [
                'status' => true,
                'message' => 'Estado de 2FA requerido actualizado correctamente',
                'required' => (int)$required
            ];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Restablece el MFA para un usuario
     * @param int $userId ID del usuario
     * @return array Resultado de la operación
     */
    public function resetMFA($userId)
    {
        try
        {
            // Cargar el usuario
            $user = R::load('admin', $userId);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Restablecer MFA
            $user->tfa_enabled = 0;
            $user->tfa_secret = NULL;
            $user->tfa_backup_codes = NULL;
            R::store($user);
            
            return ['status' => true, 'message' => 'MFA restablecido correctamente'];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Genera nuevos códigos de respaldo para un usuario
     * @param int $userId ID del usuario
     * @return array Resultado de la operación con los nuevos códigos generados
     */
    public function generateNewBackupCodes($userId)
    {
        try
        {
            require_once dirname(__DIR__, 2) . '/includes/functions/2fa_functions.php';
            
            // Cargar el usuario
            $user = R::load('admin', $userId);
            
            if (!$user->id) {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
            
            // Generar nuevos códigos
            $backupCodes = generateBackupCodes();
            
            // Guardar los nuevos códigos
            $user->tfa_backup_codes = json_encode($backupCodes);
            R::store($user);
            
            return [
                'status' => true,
                'message' => 'Códigos de respaldo generados correctamente',
                'backup_codes' => $backupCodes
            ];
        }
        catch (Exception $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}