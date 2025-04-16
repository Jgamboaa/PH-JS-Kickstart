<?php
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
            $usuario = $data['usuario'];
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $gender = $data['gender'];
            $roles_ids = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";
            $today = date("Y-m-d");

            // Manejar subida de foto
            $new_filename = $this->handlePhotoUpload($files, $usuario);

            $sql = "INSERT INTO admin (username, password, user_firstname, user_lastname, photo, created_on, roles_ids, admin_gender) 
                    VALUES (:usuario, :password, :firstname, :lastname, :new_filename, :today, :roles_ids, :gender)";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':new_filename', $new_filename);
            $stmt->bindParam(':today', $today);
            $stmt->bindParam(':roles_ids', $roles_ids);
            $stmt->bindParam(':gender', $gender);

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Usuario agregado correctamente'];
            }
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateUser($data, $files)
    {
        try
        {
            $id = $data['id'];
            $username = $data['usuario'];
            $new_password = $data['password'];
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $gender = $data['gender'];
            $roles_ids = isset($data['roles_ids']) && is_array($data['roles_ids']) ?
                implode(",", $data['roles_ids']) : "";

            // Obtener información actual del usuario
            $sql_user = "SELECT * FROM admin WHERE id = :id";
            $stmt_user = $this->conn->prepare($sql_user);
            $stmt_user->bindParam(':id', $id);
            $stmt_user->execute();
            $urow = $stmt_user->fetch();

            // Verificar si la contraseña ha cambiado
            $password_hashed = $new_password == $urow['password'] ?
                $urow['password'] : password_hash($new_password, PASSWORD_DEFAULT);

            // Manejar subida de foto
            $photo_sql = '';
            $filename = $files['photo']['name'];
            if (!empty($filename))
            {
                // Eliminar foto anterior si existe
                if ($urow['photo'] && file_exists('../../../images/admins/' . $urow['photo']))
                {
                    unlink('../../../images/admins/' . $urow['photo']);
                }
                // Subir nueva foto
                $new_filename = $this->handlePhotoUpload($files, $username);
                $photo_sql = ", photo = :photo";
            }

            // Preparar SQL según si hay foto nueva o no
            if (!empty($photo_sql))
            {
                $sql = "UPDATE admin SET 
                        username = :username, 
                        password = :password, 
                        user_firstname = :firstname, 
                        user_lastname = :lastname,
                        roles_ids = :roles_ids, 
                        admin_gender = :gender,
                        photo = :photo
                        WHERE id = :id";
            }
            else
            {
                $sql = "UPDATE admin SET 
                        username = :username, 
                        password = :password, 
                        user_firstname = :firstname, 
                        user_lastname = :lastname,
                        roles_ids = :roles_ids, 
                        admin_gender = :gender
                        WHERE id = :id";
            }

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password_hashed);
            $stmt->bindParam(':firstname', $firstname);
            $stmt->bindParam(':lastname', $lastname);
            $stmt->bindParam(':roles_ids', $roles_ids);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':id', $id);

            if (!empty($photo_sql))
            {
                $stmt->bindParam(':photo', $new_filename);
            }

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Perfil de usuario actualizado correctamente'];
            }
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateProfile($data, $files, $current_user)
    {
        try
        {
            $curr_password = $data['curr_password'];
            $password = $data['password'];
            $color_mode = $data['color_mode'];
            $username = $current_user['username'];

            // Verificar contraseña actual
            if (!password_verify($curr_password, $current_user['password']))
            {
                return ['status' => false, 'message' => 'Contraseña actual incorrecta'];
            }

            // Manejar subida de foto
            $filename = $current_user['photo'];
            if (!empty($files['photo']['name']))
            {
                // Eliminar foto anterior si existe
                if (file_exists('../../../images/admins/' . $current_user['photo']) && !empty($current_user['photo']))
                {
                    unlink('../../../images/admins/' . $current_user['photo']);
                }
                $ext = pathinfo($files['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'photo_' . $username . '.' . $ext;
                move_uploaded_file($files['photo']['tmp_name'], '../../../images/admins/' . $filename);
            }

            // Verificar si la contraseña ha cambiado
            if ($password == $current_user['password'])
            {
                $password = $current_user['password'];
            }
            else
            {
                $password = password_hash($password, PASSWORD_DEFAULT);
            }

            $sql = "UPDATE admin SET password = :password, photo = :filename, color_mode = :color_mode 
                    WHERE id = :user_id";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':color_mode', $color_mode);
            $stmt->bindParam(':user_id', $current_user['id']);

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Perfil actualizado correctamente'];
            }
        }
        catch (PDOException $e)
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

            // Obtener información del usuario
            $sql = "SELECT * FROM admin WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch();

            // Eliminar foto si existe
            if ($row['photo'] && file_exists('../../../images/admins/' . $row['photo']))
            {
                unlink('../../../images/admins/' . $row['photo']);
            }

            // Eliminar usuario
            $sql = "DELETE FROM admin WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute())
            {
                return ['status' => true, 'message' => 'Usuario eliminado correctamente'];
            }
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getUser($id)
    {
        try
        {
            $sql = "SELECT * FROM admin WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0)
            {
                return $stmt->fetch();
            }
            else
            {
                return ['status' => false, 'message' => 'Usuario no encontrado'];
            }
        }
        catch (PDOException $e)
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

            $sql = "SELECT * FROM admin WHERE id != 1";
            if ($current_user_id == 1)
            {
                $sql = "SELECT * FROM admin";
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $data = [];

            while ($row = $stmt->fetch())
            {
                $photoPath = "../../../images/admins/" . $row['photo'];
                $photoSrc = (file_exists($photoPath) && !empty($row['photo'])) ?
                    $photoPath : "../../../images/admins/profile.png";

                $acciones = '
                    <button class="btn btn-sm btn-success edit" data-id="' . $row['id'] . '">
                        <i class="fa-duotone fa-solid fa-pen fa-lg"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete" data-id="' . $row['id'] . '">
                        <i class="fa-duotone fa-solid fa-trash-xmark fa-lg"></i>
                    </button>
                ';

                $ultimo_login = !empty($row['last_login']) ?
                    date('d/m/Y - h:i:s A', strtotime($row['last_login'])) : 'No disponible';

                // Procesar roles
                $roles_mostrados = $this->formatUserRoles($row['roles_ids'], $roles_map);

                $data[] = [
                    'foto' => '<img src="' . $photoSrc . '" class="img-circle" width="40px" height="40px" loading="lazy">',
                    'nombre' => $row['user_firstname'] . ' ' . $row['user_lastname'],
                    'correo' => $row['username'],
                    'roles' => $roles_mostrados,
                    'ultimo_login' => $ultimo_login,
                    'acciones' => $acciones
                ];
            }

            return ['data' => $data];
        }
        catch (PDOException $e)
        {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function getAllRoles()
    {
        try
        {
            $roles_sql = "SELECT id, nombre FROM roles";
            $stmt = $this->conn->prepare($roles_sql);
            $stmt->execute();
            $roles_map = [];

            while ($role = $stmt->fetch())
            {
                $roles_map[$role['id']] = $role['nombre'];
            }

            return $roles_map;
        }
        catch (PDOException $e)
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
}
