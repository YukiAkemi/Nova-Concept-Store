<?php
// 1. Impedir que errores de PHP ensucien el JSON
error_reporting(0); 
ini_set('display_errors', 0);

include '../conexion/conexion.php';

// 2. Asegurar que el navegador sepa que enviamos JSON
header('Content-Type: application/json; charset=utf-8');

// 3. Limpiar cualquier espacio en blanco accidental antes del JSON
ob_clean(); 

// CONSULTA MODIFICADA: Ajustada exactamente a los campos de la imagen
$sql = "SELECT id_usuario, nombre_usuario, nombre_completo, rol FROM usuario ORDER BY nombre_completo ASC";
$result = mysqli_query($conn, $sql);

$usuarios = [];

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
}

// 4. Enviar el JSON y terminar el script inmediatamente
echo json_encode($usuarios);
exit;
?>