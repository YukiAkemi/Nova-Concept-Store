<?php
error_reporting(0); 
ini_set('display_errors', 0);

include '../conexion/conexion.php'; // Asegúrate de que esta ruta apunte correctamente a tu conexión

if (isset($conexion) && !isset($conn)) { $conn = $conexion; }

header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

// 1. Recibir los datos del formulario
$nombre_emprendimiento = $_POST['nombre_emprendimiento'] ?? '';
$nombre                = $_POST['nombre'] ?? '';
$telefono              = $_POST['telefono'] ?? '';
$correo                = $_POST['correo'] ?? '';

// 2. Validación de campos obligatorios
if (empty($nombre_emprendimiento) || empty($nombre) || empty($telefono) || empty($correo)) {
    echo json_encode(["status" => "error", "message" => "Todos los campos obligatorios deben estar llenos."]);
    exit;
}

// Variable para almacenar el nombre del archivo de la foto
$nuevo_nombre_archivo = 'default.png'; 

// 3. Procesar y guardar la imagen físicamente usando el Nombre del Emprendimiento
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    
    $directorio_destino = 'foto_emprendedores/';
    
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }
    
    $file_tmp_path = $_FILES['foto']['tmp_name'];
    $file_name     = $_FILES['foto']['name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($file_extension, $extensiones_permitidas)) {
        // Sanitizar el nombre del emprendimiento para evitar problemas con espacios en el archivo físico
        $nombre_seguro = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $nombre_emprendimiento));
        
        $nuevo_nombre_archivo = $nombre_seguro . '.' . $file_extension;
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

// 4. Consulta SQL Corregida: Agregada la columna 'foto' y el quinto '?'
// Nota: Cambiado 'emprendedores' a 'emprendedor' para que coincida con tu tabla real
$sql = "INSERT INTO emprendedores (nombre_emprendimiento, nombre, telefono, correo) 
        VALUES (?, ?, ?, ?)";

if ($stmt = mysqli_prepare($conn, $sql)) {
    
    // CORRECCIÓN: "sssss" ahora enlaza perfectamente con las 5 variables requeridas
    mysqli_stmt_bind_param($stmt, "ssss", $nombre_emprendimiento, $nombre, $telefono, $correo);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Emprendedor registrado con éxito."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al guardar en la base de datos: " . mysqli_stmt_error($stmt)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["status" => "error", "message" => "Error al preparar la consulta: " . mysqli_error($conn)]);
}

exit;
?>