<?php
include '../session.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$crud = $_POST['crud'];

	switch ($crud)
	{
		case 'create':
			$usuario = $conn->real_escape_string($_POST['usuario']);
			$password = $_POST['password'];
			$firstname = $conn->real_escape_string($_POST['firstname']);
			$lastname = $conn->real_escape_string($_POST['lastname']);
			$gender = $_POST['gender'];
			if (isset($_POST['roles_ids']) && is_array($_POST['roles_ids']))
			{
				$roles_ids = implode(",", $_POST['roles_ids']);
			}
			else
			{
				$roles_ids = "";
			}
			$today = date("Y-m-d");

			// Manejar subida de foto
			$filename = $_FILES['photo']['name'];
			if (!empty($filename))
			{
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$new_filename = 'user_' . $usuario . '_' . time() . '.' . $ext;
				move_uploaded_file($_FILES['photo']['tmp_name'], '../../../images/admins/' . $new_filename);
			}
			else
			{
				$new_filename = '';
			}

			$password_hashed = password_hash($password, PASSWORD_DEFAULT);

			$sql = "INSERT INTO admin (username, password, user_firstname, user_lastname, photo, created_on,roles_ids, admin_gender) 
                VALUES ('$usuario', '$password_hashed', '$firstname', '$lastname', '$new_filename',  '$today', '$roles_ids', '$gender')";

			if ($conn->query($sql))
			{
				echo json_encode(['status' => true, 'message' => 'Usuario agregado correctamente']);
			}
			else
			{
				echo json_encode(['status' => false, 'message' => $conn->error]);
			}
			break;

		case 'edit':
			$id = $_POST['id'];
			$username = $conn->real_escape_string($_POST['usuario']);
			$new_password = $_POST['password'];
			$firstname = $conn->real_escape_string($_POST['firstname']);
			$lastname = $conn->real_escape_string($_POST['lastname']);
			$gender = $_POST['gender'];

			if (isset($_POST['roles_ids']) && is_array($_POST['roles_ids']))
			{
				$roles_ids = implode(",", $_POST['roles_ids']);
			}
			else
			{
				$roles_ids = "";
			}

			// Manejar subida de foto si se ha proporcionado
			$sql_user = "SELECT * FROM admin WHERE id = '$id'";
			$result = $conn->query($sql_user);
			$urow = $result->fetch_assoc();

			$password_actual = $urow['password'];

			if ($new_password == $urow['password'])
			{
				$password_hashed = $urow['password'];
			}
			else
			{
				$password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
			}

			// Manejar subida de foto
			$filename = $_FILES['photo']['name'];
			if (!empty($filename))
			{
				// Eliminar foto anterior
				if ($urow['photo'] && file_exists('../../../images/admins/' . $urow['photo']))
				{
					unlink('../../../images/admins/' . $urow['photo']);
				}
				// Subir nueva foto
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$new_filename = 'user_' . $username . '_' . time() . '.' . $ext;
				move_uploaded_file($_FILES['photo']['tmp_name'], '../../../images/admins/' . $new_filename);
				$photo_sql = ", photo = '$new_filename'";
			}
			else
			{
				$photo_sql = '';
			}

			// Actualizar usuario
			$sql = "UPDATE admin SET 
                        username = '$username', 
                        password = '$password_hashed', 
                        user_firstname = '$firstname', 
                        user_lastname = '$lastname',
                        roles_ids = '$roles_ids', 
                        admin_gender = '$gender'" .
				$photo_sql .
				" WHERE id = '$id'";

			if ($conn->query($sql))
			{
				echo json_encode(['status' => true, 'message' => 'Perfil de usuario actualizado correctamente']);
			}
			else
			{
				echo json_encode(['status' => false, 'message' => $conn->error]);
			}
			break;

		case 'delete':
			$id = $_POST['id'];
			if ($id == $user['id'])
			{
				echo json_encode(['status' => false, 'message' => 'No puedes eliminar tu propio usuario']);
				exit();
			}
			$sql = "SELECT * FROM admin WHERE id = '$id'";
			$query = $conn->query($sql);
			$row = $query->fetch_assoc();
			if ($row['photo'] && file_exists('../../../images/admins/' . $row['photo']))
			{
				unlink('../../../images/admins/' . $row['photo']);
			}

			$sql = "DELETE FROM admin WHERE id = '$id'";
			if ($conn->query($sql))
			{
				echo json_encode(['status' => true, 'message' => 'Usuario eliminado correctamente']);
			}
			else
			{
				echo json_encode(['status' => false, 'message' => $conn->error]);
			}
			break;

		case 'get':
			$id = $_POST['id'];
			$sql = "SELECT * FROM admin WHERE id = '$id'";
			$query = $conn->query($sql);
			if ($query->num_rows > 0)
			{
				$row = $query->fetch_assoc();
				echo json_encode($row);
			}
			else
			{
				echo json_encode(['status' => false, 'message' => 'Usuario no encontrado']);
			}
			break;

		default:
			echo json_encode(['status' => false, 'message' => 'Acción no válida']);
			break;
	}
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
	// Primero obtenemos todos los roles disponibles
	$roles_sql = "SELECT id, nombre FROM roles";
	$roles_query = $conn->query($roles_sql);
	$roles_map = [];
	while ($role = $roles_query->fetch_assoc())
	{
		$roles_map[$role['id']] = $role['nombre'];
	}

	$sql = "SELECT * FROM admin WHERE id != 1";
	if ($user['id'] == 1)
	{
		$sql = "SELECT * FROM admin";
	}
	$query = $conn->query($sql);
	$data = [];
	while ($row = $query->fetch_assoc())
	{
		$photoPath = "../../../images/admins/" . $row['photo'];
		$photoSrc = (file_exists($photoPath) && !empty($row['photo'])) ? $photoPath : "../../../images/admins/profile.png";

		$acciones = '
            <button class="btn btn-sm btn-success edit" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>
            <button class="btn btn-sm btn-danger delete" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-trash-xmark fa-lg"></i></button>
        ';

		$ultimo_login = !empty($row['last_login']) ? date('d/m/Y - h:i:s A', strtotime($row['last_login'])) : 'No disponible';

		// Procesar los roles para mostrar nombres en lugar de IDs
		$roles_mostrados = '';
		if (!empty($row['roles_ids']))
		{
			$roles_ids_array = explode(',', $row['roles_ids']);
			$roles_nombres = [];

			foreach ($roles_ids_array as $role_id)
			{
				if (isset($roles_map[$role_id]))
				{
					$roles_nombres[] = $roles_map[$role_id];
				}
			}

			if (!empty($roles_nombres))
			{
				$roles_mostrados = '<ul class="mb-0">';
				foreach ($roles_nombres as $nombre)
				{
					$roles_mostrados .= '<li>' . $nombre . '</li>';
				}
				$roles_mostrados .= '</ul>';
			}
			else
			{
				$roles_mostrados = 'Sin roles';
			}
		}
		else
		{
			$roles_mostrados = 'Sin roles';
		}

		$data[] = [
			'foto' => '<img src="' . $photoSrc . '" class="img-circle" width="40px" height="40px" loading="lazy">',
			'nombre' => $row['user_firstname'] . ' ' . $row['user_lastname'],
			'correo' => $row['username'],
			'roles' => $roles_mostrados,
			'ultimo_login' => $ultimo_login,
			'acciones' => $acciones
		];
	}
	echo json_encode(['data' => $data]);
}
