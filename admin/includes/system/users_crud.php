<?php
include '../session.php';
include '../../controllers/system/users.php';

$userController = new UserController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$crud = $_POST['crud'];

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

		default:
			echo json_encode(['status' => false, 'message' => 'Acción no válida']);
			break;
	}
}

if (isset($_GET['crud']) && $_GET['crud'] === 'fetch')
{
	$result = $userController->getAllUsers($user['id']);
	echo json_encode($result);
}
