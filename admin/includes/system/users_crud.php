<?php
include '../session.php';
include '../../controllers/system/users.php';

$userController = new UserController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$crud = $_POST['crud'] ?? '';

	switch ($crud)
	{
		case 'create':
			$result = $userController->createUser($_POST, $_FILES);
			echo json_encode($result);
			break;

		case 'edit':
			$result = $userController->updateUser($_POST, $_FILES);
			echo json_encode($result);
			break;

		case 'delete':
			$result = $userController->deleteUser($_POST['id'], $user['id']);
			echo json_encode($result);
			break;

		case 'get':
			$result = $userController->getUser($_POST['id']);
			echo json_encode($result);
			break;

		case 'profile':
			$result = $userController->updateProfile($_POST, $_FILES, $user);
			echo json_encode($result);
			break;

		default:
			// Si no se especificó el parámetro crud, intentar procesar como actualización de perfil
			if (isset($_POST['curr_password']))
			{
				$result = $userController->updateProfile($_POST, $_FILES, $user);
				echo json_encode($result);
			}
			else
			{
				echo json_encode(['status' => false, 'message' => 'Acción no válida']);
			}
			break;
	}
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
	$result = $userController->getAllUsers($user['id']);
	echo json_encode($result);
}
