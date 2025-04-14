<?php
include '../session.php';

$response = ['status' => false, 'message' => ''];

if (isset($_POST['curr_password'])) {
    $curr_password = $_POST['curr_password'];
    $password = $_POST['password'];
    $photo = $_FILES['photo']['name'];
    $color_mode = $_POST['color_mode'];
    $username = $user['username'];

    if (password_verify($curr_password, $user['password'])) {
        if (!empty($photo)) {
            if (file_exists('../../../images/admins/' . $user['photo']) && !empty($user['photo'])) {
                unlink('../../../images/admins/' . $user['photo']);
            }
            $ext = pathinfo($photo, PATHINFO_EXTENSION);
            $filename = 'photo_' . $username . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../../../images/admins/' . $filename);
        } else {
            $filename = $user['photo'];
        }

        if ($password == $user['password']) {
            $password = $user['password'];
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = "UPDATE admin SET password = '$password', photo = '$filename', color_mode ='$color_mode' WHERE id = '" . $user['id'] . "'";
        
        if ($conn->query($sql)) {
            $response['status'] = true;
            $response['message'] = 'Perfil actualizado correctamente';
        } else {
            $response['message'] = 'Error al actualizar: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Contraseña actual incorrecta';
    }
} else {
    $response['message'] = 'Datos incompletos';
}

echo json_encode($response);
