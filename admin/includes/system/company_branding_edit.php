<?php
include '../session.php';

$response = array();

if (isset($_FILES)) {
    try {
        if(isset($_FILES['logo']) && !empty($_FILES['logo']['name'])) {
            unlink('../../../images/logo.png');
            move_uploaded_file($_FILES['logo']['tmp_name'], '../../../images/logo.png');
        }
        
        if(isset($_FILES['logo2']) && !empty($_FILES['logo2']['name'])) {
            unlink('../../../images/logo2.png'); 
            move_uploaded_file($_FILES['logo2']['tmp_name'], '../../../images/logo2.png');
        }
        
        if(isset($_FILES['logo_circulo']) && !empty($_FILES['logo_circulo']['name'])) {
            unlink('../../../images/logo_circulo.png');
            move_uploaded_file($_FILES['logo_circulo']['tmp_name'], '../../../images/logo_circulo.png');
        }
        
        if(isset($_FILES['favicon']) && !empty($_FILES['favicon']['name'])) {
            unlink('../../../images/favicon.png');
            move_uploaded_file($_FILES['favicon']['tmp_name'], '../../../images/favicon.png');
        }
        
        if(isset($_FILES['avatar']) && !empty($_FILES['avatar']['name'])) {
            unlink('../../../images/avatar.png');
            move_uploaded_file($_FILES['avatar']['tmp_name'], '../../../images/avatar.png');
        }

        $response['status'] = 'success';
        $response['message'] = 'Imágenes actualizadas exitosamente';
    } catch(Exception $e) {
        $response['status'] = 'error';
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'No se recibieron archivos';
}

echo json_encode($response);
