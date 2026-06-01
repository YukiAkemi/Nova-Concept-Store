<?php
error_reporting(0); 
ini_set('display_errors', 0);

include '../conexion/conexion.php'; 

if (isset($conn) && !isset($conexion)) { $conexion = $conn; }

header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

$id_emprendedor = $_POST['id_emprendedor'] ?? '';

if (empty($id_emprendedor)) {
    echo json_encode(["status" => "error", "message" => "No se proporcionó un ID de emprendedor válido."]);
    exit;
}

// 1. OBTENER EL NOMBRE DEL EMPRENDIMIENTO ANTES DE ELIMINARLO
$nombre_emprendimiento = "";
$sql_buscar = "SELECT nombre_emprendimiento FROM emprendedores WHERE id_emprendedor = ?";

if ($stmt_buscar = mysqli_prepare($conexion, $sql_buscar)) {
    mysqli_stmt_bind_param($stmt_buscar, "i", $id_emprendedor);
    mysqli_stmt_execute($stmt_buscar);
    mysqli_stmt_bind_result($stmt_buscar, $nombre_emprendimiento);
    mysqli_stmt_fetch($stmt_buscar);
    mysqli_stmt_close($stmt_buscar);
}

// 2. SI ENCONTRAMOS AL EMPRENDEDOR, BORRAR SUS IMÁGENES DE LA CARPETA 'foto_emprendedores/'
if (!empty($nombre_emprendimiento)) {
    $carpeta = 'foto_emprendedores/';
    $formatos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    // Sanitizar el nombre del emprendimiento igual que al registrar (espacios por guiones bajos)
    $nombre_seguro = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $nombre_emprendimiento));
    
    // Recorremos los formatos posibles para borrar el archivo físico del servidor
    foreach ($formatos as $ext) {
        $ruta_imagen = $carpeta . $nombre_seguro . '.' . $ext;
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen); // Elimina el archivo físico
        }
    }
}

// 3. ELIMINAR EL REGISTRO DE LA BASE DE DATOS
$sql_delete = "DELETE FROM emprendedores WHERE id_emprendedor = ?";

if ($stmt_delete = mysqli_prepare($conexion, $sql_delete)) {
    mysqli_stmt_bind_param($stmt_delete, "i", $id_emprendedor);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        echo json_encode(["status" => "success", "message" => "Emprendedor e imagen eliminados correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al intentar eliminar el emprendedor: " . mysqli_stmt_error($stmt_delete)]);
    }
    
    mysqli_stmt_close($stmt_delete);
} else {
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de eliminación: " . mysqli_error($conexion)]);
}

exit;
?>