<?php
error_reporting(0); 
ini_set('display_errors', 0);

include '../conexion/conexion.php'; 

if (isset($conn) && !isset($conexion)) { $conexion = $conn; }

header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

$id_usuario = $_POST['id_usuario'] ?? '';

if (empty($id_usuario)) {
    echo json_encode(["status" => "error", "message" => "No se proporcionó un ID de usuario válido."]);
    exit;
}

// 1. OBTENER EL NOMBRE DE USUARIO ANTES DE ELIMINARLO
$nombre_usuario = "";
$sql_buscar = "SELECT nombre_usuario FROM usuario WHERE id_usuario = ?";

if ($stmt_buscar = mysqli_prepare($conexion, $sql_buscar)) {
    mysqli_stmt_bind_param($stmt_buscar, "i", $id_usuario);
    mysqli_stmt_execute($stmt_buscar);
    mysqli_stmt_bind_result($stmt_buscar, $nombre_usuario);
    mysqli_stmt_fetch($stmt_buscar);
    mysqli_stmt_close($stmt_buscar);
}

// 2. SI ENCONTRAMOS AL USUARIO, BORRAR SUS IMÁGENES DE LA CARPETA 'foto/'
if (!empty($nombre_usuario)) {
    $carpeta = 'foto/';
    $formatos = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    
    // Recorremos los formatos posibles para borrar el archivo físico del servidor
    foreach ($formatos as $ext) {
        $ruta_imagen = $carpeta . $nombre_usuario . '.' . $ext;
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen); // Elimina el archivo físico
        }
    }
}

// 3. ELIMINAR EL REGISTRO DE LA BASE DE DATOS
$sql_delete = "DELETE FROM usuario WHERE id_usuario = ?";

if ($stmt_delete = mysqli_prepare($conexion, $sql_delete)) {
    mysqli_stmt_bind_param($stmt_delete, "i", $id_usuario);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        echo json_encode(["status" => "success", "message" => "Usuario e imagen eliminados correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al intentar eliminar el usuario: " . mysqli_stmt_error($stmt_delete)]);
    }
    
    mysqli_stmt_close($stmt_delete);
} else {
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de eliminación: " . mysqli_error($conexion)]);
}

exit;
?>