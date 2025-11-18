<?php

class UserController
{
    /**
     * @var PDO
     */
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createUser($data, $files)
    {
        try
        {
            // Preparar datos del usuario (admin)
            $username      = $data['usuario'];
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $firstname     = $data['firstname'];
            $lastname      = $data['lastname'];
            $gender        = $data['gender'];
            $roles_ids     = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";
            $created_on    = date("Y-m-d");
            $tfa_required  = isset($data['tfa_required']) ? (int)$data['tfa_required'] : 0;

            // Manejar subida de foto
            $photo = $this->handlePhotoUpload($files, $data['usuario']);

            // Guardar el usuario usando PDO
            $stmt = $this->pdo->prepare('
                INSERT INTO admin (
                    username, password, user_firstname, user_lastname,
                    admin_gender, roles_ids, created_on, tfa_required, photo
                ) VALUES (
                    :username, :password, :user_firstname, :user_lastname,
                    :admin_gender, :roles_ids, :created_on, :tfa_required, :photo
                )
            ');

            $stmt->execute([
                ':username'       => $username,
                ':password'       => $password_hash,
                ':user_firstname' => $firstname,
                ':user_lastname'  => $lastname,
                ':admin_gender'   => $gender,
                ':roles_ids'      => $roles_ids,
                ':created_on'     => $created_on,
                ':tfa_required'   => $tfa_required,
                ':photo'          => $photo,
            ]);

            $id = $this->pdo->lastInsertId();

            if ($id)
            {
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
            $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $data['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Verificar si la contraseña ha cambiado
            $password = $data['password'] == $user['password']
                ? $user['password']
                : password_hash($data['password'], PASSWORD_DEFAULT);

            // Actualizar propiedades
            $username     = $data['usuario'];
            $firstname    = $data['firstname'];
            $lastname     = $data['lastname'];
            $gender       = $data['gender'];
            $roles_ids    = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";
            $tfa_required = isset($data['tfa_required']) ? (int)$data['tfa_required'] : 0;

            // Manejar subida de foto
            $photo = $user['photo'];
            $filename = $files['photo']['name'];
            if (!empty($filename))
            {
                // Eliminar foto anterior si existe
                if (!empty($user['photo']) && file_exists('../../../images/admins/' . $user['photo']))
                {
                    unlink('../../../images/admins/' . $user['photo']);
                }
                // Subir nueva foto
                $photo = $this->handlePhotoUpload($files, $data['usuario']);
            }

            // Guardar cambios usando PDO
            $stmtUpdate = $this->pdo->prepare('
                UPDATE admin SET
                    username = :username,
                    password = :password,
                    user_firstname = :user_firstname,
                    user_lastname = :user_lastname,
                    admin_gender = :admin_gender,
                    roles_ids = :roles_ids,
                    tfa_required = :tfa_required,
                    photo = :photo
                WHERE id = :id
            ');

            $stmtUpdate->execute([
                ':username'       => $username,
                ':password'       => $password,
                ':user_firstname' => $firstname,
                ':user_lastname'  => $lastname,
                ':admin_gender'   => $gender,
                ':roles_ids'      => $roles_ids,
                ':tfa_required'   => $tfa_required,
                ':photo'          => $photo,
                ':id'             => $data['id'],
            ]);

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
            if (!password_verify($data['curr_password'], $current_user['password']))
            {
                return ['status' => false, 'message' => 'Contraseña actual incorrecta'];
            }

            // Cargar el usuario
            $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $current_user['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Verificar si la contraseña ha cambiado
            $password = $data['password'] == $current_user['password']
                ? $current_user['password']
                : password_hash($data['password'], PASSWORD_DEFAULT);

            $color_mode = $data['color_mode'];
            $photo      = $user['photo'];

            // Manejar subida de foto
            if (!empty($files['photo']['name']))
            {
                // Eliminar foto anterior si existe
                if (!empty($current_user['photo']) && file_exists('../../../images/admins/' . $current_user['photo']))
                {
                    unlink('../../../images/admins/' . $current_user['photo']);
                }
                $ext = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'photo_' . $current_user['username'] . '.' . $ext;
                move_uploaded_file($files['photo']['tmp_name'], '../../../images/admins/' . $filename);
                $photo = $filename;
            }

            // Guardar cambios usando PDO
            $stmtUpdate = $this->pdo->prepare('
                UPDATE admin SET
                    password = :password,
                    color_mode = :color_mode,
                    photo = :photo
                WHERE id = :id
            ');

            $stmtUpdate->execute([
                ':password'   => $password,
                ':color_mode' => $color_mode,
                ':photo'      => $photo,
                ':id'         => $current_user['id'],
            ]);

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
            if ($id == $current_user_id)
            {
                return ['status' => false, 'message' => 'No puedes eliminar tu propio usuario'];
            }

            // Cargar el usuario
            $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Eliminar foto si existe
            if (!empty($user['photo']) && file_exists('../../../images/admins/' . $user['photo']))
            {
                unlink('../../../images/admins/' . $user['photo']);
            }

            // Eliminar usuario
            $stmtDelete = $this->pdo->prepare('DELETE FROM admin WHERE id = :id');
            $stmtDelete->execute([':id' => $id]);

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
            // Cargar el usuario por ID usando PDO
            $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user)
            {
                return $user;
            }
            else
            {
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

            // Obtener todos los usuarios usando PDO
            if ($current_user_id != 1)
            {
                $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id != 1');
                $stmt->execute();
            }
            else
            {
                $stmt = $this->pdo->prepare('SELECT * FROM admin');
                $stmt->execute();
            }

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = [];
            foreach ($users as $user)
            {
                $photoPath = "../../../images/admins/" . $user['photo'];
                $photoSrc = (file_exists($photoPath) && !empty($user['photo'])) ?
                    $photoPath : "../../../images/admins/profile.png";

                $acciones = '
                    <button class="btn btn-sm btn-success edit" data-id="' . $user['id'] . '" data-toggle="tooltip" title="Editar Usuario">
                        <i class="fa-duotone fa-solid fa-pen fa-lg"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="' . $user['id'] . '" data-toggle="tooltip" title="Eliminar Usuario">
                        <i class="fa-duotone fa-solid fa-trash-xmark fa-lg"></i>
                    </button>
                ';

                // Añadir botones para gestionar 2FA
                if ($current_user_id != $user['id'])
                {
                    $acciones .= '
                        <button class="btn btn-sm btn-info reset-2fa" data-id="' . $user['id'] . '" data-toggle="tooltip" title="Restablecer 2FA">
                            <i class="fa-duotone fa-solid fa-shield-check fa-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-warning new-backup-codes" data-id="' . $user['id'] . '" data-toggle="tooltip" title="Generar códigos de respaldo">
                            <i class="fa-duotone fa-solid fa-key fa-lg"></i>
                        </button>
                    ';
                }

                $ultimo_login = !empty($user['last_login']) ?
                    date('d/m/Y - h:i:s A', strtotime($user['last_login'])) : 'No disponible';

                // Procesar roles
                $roles_mostrados = $this->formatUserRoles($user['roles_ids'], $roles_map);

                // Estado MFA
                $mfa_status = $user['tfa_enabled'] ?
                    '<span class="badge badge-success">Activado</span>' :
                    '<span class="badge badge-danger">Desactivado</span>';

                // MFA Requerido
                $mfa_required = $user['tfa_required'] ?
                    '<span class="badge badge-primary">Obligatorio</span>' :
                    '<span class="badge badge-secondary">Opcional</span>';

                $data[] = [
                    'foto' => '<img src="' . $photoSrc . '" class="img-circle" width="40px" height="40px" loading="lazy">',
                    'nombre' => $user['user_firstname'] . ' ' . $user['user_lastname'],
                    'correo' => $user['username'],
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
            // Obtener todos los roles usando PDO
            $stmt = $this->pdo->prepare('SELECT id, nombre FROM roles');
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_OBJ);
            $roles_map = [];

            foreach ($roles as $role)
            {
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
            $stmt = $this->pdo->prepare('SELECT id FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Actualizar el estado de 2FA requerido
            $stmtUpdate = $this->pdo->prepare('UPDATE admin SET tfa_required = :required WHERE id = :id');
            $stmtUpdate->execute([
                ':required' => (int)$required,
                ':id'       => $userId,
            ]);

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
            $stmt = $this->pdo->prepare('SELECT id FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Restablecer MFA
            $stmtUpdate = $this->pdo->prepare('
                UPDATE admin SET 
                    tfa_enabled = 0,
                    tfa_secret = NULL,
                    tfa_backup_codes = NULL
                WHERE id = :id
            ');
            $stmtUpdate->execute([':id' => $userId]);

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
            $stmt = $this->pdo->prepare('SELECT id FROM admin WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user)
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }

            // Generar nuevos códigos
            $backupCodes = generateBackupCodes();

            // Guardar los nuevos códigos
            $stmtUpdate = $this->pdo->prepare('UPDATE admin SET tfa_backup_codes = :codes WHERE id = :id');
            $stmtUpdate->execute([
                ':codes' => json_encode($backupCodes),
                ':id'    => $userId,
            ]);

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
