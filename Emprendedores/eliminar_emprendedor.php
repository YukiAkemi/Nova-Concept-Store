<?php
include 'conexion.php';
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "No se pudo eliminar"];

if (isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    $sql = "DELETE FROM emprendedor WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        $response["status"] = "success";
    } else {
        $response["message"] = mysqli_error($conn);
    }
}

echo json_encode($response);
?>