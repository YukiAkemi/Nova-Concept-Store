<?php
include 'conexion.php'; // Asegúrate de que este archivo tenga tu $conn
header('Content-Type: application/json');

try {
    $query = "SELECT id, nombre_marca, nombre_responsable FROM emprendedor ORDER BY nombre_marca ASC";
    $resultado = mysqli_query($conn, $query);
    
    $emprendedores = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $emprendedores[] = $fila;
    }
    
    echo json_encode($emprendedores);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>