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
                VALUES ('$usuario', '$password', '$firstname', '$lastname', '$new_filename', '$today', '$roles_ids', '$gender')";

        if ($this->conn->query($sql))
        {
            return ['status' => true, 'message' => 'Usuario agregado correctamente'];
        }
        else
        {
            return ['status' => false, 'message' => $this->conn->error];
        }
    }

    public function updateUser($data, $files)
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
        $sql_user = "SELECT * FROM admin WHERE id = '$id'";
        $result = $this->conn->query($sql_user);
        $urow = $result->fetch_assoc();

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
            $photo_sql = ", photo = '$new_filename'";
        }

        // Actualizar usuario
        $sql = "UPDATE admin SET 
                username = '$username', 
                password = '$password_hashed', 
                user_firstname = '$firstname', 
                user_lastname = '$lastname',
                roles_ids = '$roles_ids', 
                admin_gender = '$gender'" . $photo_sql . " 
                WHERE id = '$id'";

        if ($this->conn->query($sql))
        {
            return ['status' => true, 'message' => 'Perfil de usuario actualizado correctamente'];
        }
        else
        {
            return ['status' => false, 'message' => $this->conn->error];
        }
    }

    public function deleteUser($id, $current_user_id)
    {
        // No permitir que un usuario se elimine a sí mismo
        if ($id == $current_user_id)
        {
            return ['status' => false, 'message' => 'No puedes eliminar tu propio usuario'];
        }

        // Obtener información del usuario
        $sql = "SELECT * FROM admin WHERE id = '$id'";
        $query = $this->conn->query($sql);
        $row = $query->fetch_assoc();

        // Eliminar foto si existe
        if ($row['photo'] && file_exists('../../../images/admins/' . $row['photo']))
        {
            unlink('../../../images/admins/' . $row['photo']);
        }

        // Eliminar usuario
        $sql = "DELETE FROM admin WHERE id = '$id'";
        if ($this->conn->query($sql))
        {
            return ['status' => true, 'message' => 'Usuario eliminado correctamente'];
        }
        else
        {
            return ['status' => false, 'message' => $this->conn->error];
        }
    }

    public function getUser($id)
    {
        $sql = "SELECT * FROM admin WHERE id = '$id'";
        $query = $this->conn->query($sql);

        if ($query->num_rows > 0)
        {
            return $query->fetch_assoc();
        }
        else
        {
            return ['status' => false, 'message' => 'Usuario no encontrado'];
        }
    }

    public function getAllUsers($current_user_id)
    {
        // Obtener todos los roles para mostrar sus nombres
        $roles_map = $this->getAllRoles();

        $sql = "SELECT * FROM admin WHERE id != 1";
        if ($current_user_id == 1)
        {
            $sql = "SELECT * FROM admin";
        }

        $query = $this->conn->query($sql);
        $data = [];

        while ($row = $query->fetch_assoc())
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

    private function getAllRoles()
    {
        $roles_sql = "SELECT id, nombre FROM roles";
        $roles_query = $this->conn->query($roles_sql);
        $roles_map = [];

        while ($role = $roles_query->fetch_assoc())
        {
            $roles_map[$role['id']] = $role['nombre'];
        }

        return $roles_map;
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
