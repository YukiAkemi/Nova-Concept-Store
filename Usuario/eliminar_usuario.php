<?php
include 'conexion.php';
header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    $sql = "DELETE FROM usuario WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No se recibió el ID"]);
}
?>