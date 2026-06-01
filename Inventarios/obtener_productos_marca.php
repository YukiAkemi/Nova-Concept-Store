<?php
header('Content-Type: application/json; charset=utf-8');

// 1. CONFIGURACIÓN DE LA CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; // <--- Cambia esto por el nombre real de tu BD

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    echo json_encode(["error" => "Error de conexión: " . mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($conexion, "utf8");

// 2. VALIDAR LA ID RECIBIDA DESDE LA URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["error" => "ID de emprendedor no proporcionada."]);
    mysqli_close($conexion);
    exit;
}

$id_emprendedor = (int)$_GET['id'];

// 3. CONSULTA SQL CON LAS COLUMNAS REALES DE TU CAPTURA
$query = "SELECT id_producto, nombre, descripcion, precio_unitario, id_categoria 
          FROM producto 
          WHERE id_emprendedor = $id_emprendedor";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    echo json_encode(["error" => "Error en la consulta: " . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit;
}

// 4. CONSTRUCCIÓN DEL ARREGLO DE DATOS
$productos = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $fila['precio_unitario'] = (float)$fila['precio_unitario'];
    $fila['id_categoria']    = (int)$fila['id_categoria'];
    $productos[] = $fila;
}

echo json_encode($productos);

mysqli_close($conexion);
?>