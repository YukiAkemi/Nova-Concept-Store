<?php
// Forzar que la respuesta del navegador sea estrictamente JSON
header('Content-Type: application/json; charset=utf-8');

// 1. CONFIGURACIÓN DE LA CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; 

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    echo json_encode(["success" => false, "error" => "Error de conexión base de datos"]);
    exit;
}
mysqli_set_charset($conexion, "utf8");

// 2. VALIDAR ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["success" => false, "error" => "ID de producto ausente."]);
    mysqli_close($conexion);
    exit;
}

$id_producto = (int)$_GET['id'];

// 3. CONSULTA EN TABLA 'producto'
$query = "DELETE FROM producto WHERE id_producto = $id_producto";

if (mysqli_query($conexion, $query)) {
    if (mysqli_affected_rows($conexion) > 0) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "El producto no existe en el inventario."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Error de servidor SQL: " . mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>