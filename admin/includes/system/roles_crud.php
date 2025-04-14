<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include '../session.php';
    $crud = $_POST['crud'];

    switch ($crud) {
        case 'create':
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $sql = "INSERT INTO roles (nombre) VALUES ('$nombre')";
            $result = $conn->query($sql);
            echo json_encode(['status' => $result, 'message' => $result ? 'Rol añadido' : $conn->error]);
            break;

        case 'edit':
            $id = $conn->real_escape_string($_POST['id']);
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $sql = "UPDATE roles SET nombre='$nombre' WHERE id='$id'";
            $result = $conn->query($sql);
            echo json_encode(['status' => $result, 'message' => $result ? 'Rol actualizado' : $conn->error]);
            break;

        case 'get':
            $id = $conn->real_escape_string($_POST['id']);
            $sql = "SELECT * FROM roles WHERE id='$id'";
            $query = $conn->query($sql);
            echo json_encode($query->fetch_assoc());
            break;
    }
} elseif (isset($_GET['crud']) && $_GET['crud'] === 'fetch') {
    include '../session.php';
    $sql = "SELECT * FROM roles";
    $query = $conn->query($sql);
    $data = [];
    while ($row = $query->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'actions' => '<button class="btn btn-success btn-sm edit-btn" data-id="' . $row['id'] . '"><i class="fa-duotone fa-solid fa-pen fa-lg"></i></button>'
        ];
    }
    echo json_encode(['data' => $data]);
}
?>