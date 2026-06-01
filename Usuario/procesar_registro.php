<?php
error_reporting(0); 
ini_set('display_errors', 0);

include '../conexion/conexion.php'; 

if (isset($conexion) && !isset($conn)) { $conn = $conexion; }

header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

// 1. Recibir los datos del formulario
$nombre_completo = $_POST['nombre_completo'] ?? '';
$nombre_usuario  = $_POST['nombre_usuario'] ?? '';
$contrasena      = $_POST['contraseña_usuario'] ?? '';
$rol             = $_POST['rol'] ?? '';

// 2. Validación de campos obligatorios
if (empty($nombre_completo) || empty($nombre_usuario) || empty($contrasena) || empty($rol)) {
    echo json_encode(["status" => "error", "message" => "Todos los campos obligatorios deben estar llenos."]);
    exit;
}

// 3. Procesar y guardar la imagen físicamente usando el Nombre de Usuario
if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] === UPLOAD_ERR_OK) {
    
    $directorio_destino = 'foto/';
    
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }
    
    $file_tmp_path = $_FILES['foto_usuario']['tmp_name'];
    $file_name     = $_FILES['foto_usuario']['name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($file_extension, $extensiones_permitidas)) {
        // El nombre del archivo físico será exactamente el nombre del usuario (Ej: LizethV.jpg)
        $nuevo_nombre_archivo = $nombre_usuario . '.' . $file_extension;
        $dest_path = $directorio_destino . $nuevo_nombre_archivo;
        
        if (!move_uploaded_file($file_tmp_path, $dest_path)) {
            echo json_encode(["status" => "error", "message" => "Error al guardar la foto en la carpeta."]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Formato de imagen no permitido."]);
        exit;
    }
}

// 4. Consulta SQL Original (SIN columna de foto, limpia como tu BD)
$sql = "INSERT INTO usuario (nombre_usuario, contraseña_usuario, nombre_completo, rol) 
        VALUES (?, ?, ?, ?)";

if ($stmt = mysqli_prepare($conn, $sql)) {
    
    // Volvemos a "ssss" para los 4 campos iniciales
    mysqli_stmt_bind_param($stmt, "ssss", $nombre_usuario, $contrasena, $nombre_completo, $rol);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Usuario registrado con éxito."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos: " . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta: " . mysqli_error($conn)]);
}

exit;
?>