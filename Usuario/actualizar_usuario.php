<?php
// actualizar_usuario.php

// 1. Evitar que errores o advertencias de PHP ensucien la respuesta JSON
error_reporting(0); 
ini_set('display_errors', 0);

// 2. Incluir conexión original de tu proyecto
include '../conexion/conexion.php'; 

// Si tu archivo usa '$conn', se mapea automáticamente a '$conexion' para evitar conflictos
if (isset($conn) && !isset($conexion)) { $conexion = $conn; }

// 3. Configurar cabeceras para responder exclusivamente en formato JSON
header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

// Función para limpiar el nombre del archivo (evita espacios, acentos y caracteres especiales)
function limpiarNombreArchivo($texto) {
    $buscar     = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', ' ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', '_');
    $limpio = str_replace($buscar, $reemplazar, $texto);
    return strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', $limpio));
}

// 4. Procesar la petición POST desde el JavaScript
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capturar y limpiar variables (usando mysqli_real_escape_string para evitar inyecciones)
    $id_usuario         = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
    $nombre_completo    = isset($_POST["nombre_completo"]) ? mysqli_real_escape_string($conexion, trim($_POST["nombre_completo"])) : '';
    $nombre_usuario     = isset($_POST["nombre_usuario"]) ? mysqli_real_escape_string($conexion, trim($_POST["nombre_usuario"])) : '';
    $contraseña_usuario = isset($_POST["contraseña_usuario"]) ? mysqli_real_escape_string($conexion, $_POST["contraseña_usuario"]) : '';
    $rol                = isset($_POST["rol"]) ? mysqli_real_escape_string($conexion, $_POST["rol"]) : '';

    // Validación estricta de campos obligatorios
    if ($id_usuario <= 0 || empty($nombre_completo) || empty($nombre_usuario) || empty($rol)) {
        echo json_encode(["status" => "error", "message" => "Todos los campos (excepto la contraseña) son obligatorios."]);
        exit;
    }

    // Carpeta local de almacenamiento físico de fotos de usuarios
    $uploadFileDir = 'foto/'; 
    $baseNuevoNombre = limpiarNombreArchivo($nombre_usuario);

    // OBTENER EL NOMBRE VIEJO DEL USUARIO (Para rastrear su archivo físico actual en la carpeta)
    $query_viejo = "SELECT nombre_usuario FROM usuario WHERE id_usuario = $id_usuario";
    $res_viejo = mysqli_query($conexion, $query_viejo);
    $data_vieja = mysqli_fetch_assoc($res_viejo);
    
    if (!$data_vieja) {
        echo json_encode(["status" => "error", "message" => "El usuario que intentas modificar no existe."]);
        exit;
    }

    $nombreViejoLimpio = limpiarNombreArchivo($data_vieja['nombre_usuario']);

    // --- MANEJO FÍSICO DE LA IMAGEN EN EL SERVIDOR ---
    
    // CASO A: EL USUARIO SÍ SUBIÓ UNA FOTO NUEVA
    if (isset($_FILES['foto_usuario']) && $_FILES['foto_usuario']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['foto_usuario']['tmp_name'];
        $fileName = $_FILES['foto_usuario']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = $baseNuevoNombre . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            
            // 1. ELIMINAR LA FOTO ANTERIOR: Buscamos en la carpeta cualquier extensión con el nombre viejo y la borramos
            if (!empty($nombreViejoLimpio)) {
                $fotosViejas = glob($uploadFileDir . $nombreViejoLimpio . '.*');
                if ($fotosViejas) {
                    foreach ($fotosViejas as $fotoVieja) {
                        if (file_exists($fotoVieja)) {
                            unlink($fotoVieja); // Borrado físico del archivo viejo
                        }
                    }
                }
            }
            
            // 2. GUARDAR LA FOTO NUEVA
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            move_uploaded_file($fileTmpPath, $dest_path);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido. Solo se actualizarán los campos de texto.']);
            exit;
        }
        
    } else {
        // CASO B: NO SUBIÓ FOTO NUEVA (Solo renombramos el archivo existente si el nombre de usuario cambió)
        if (!empty($nombreViejoLimpio) && $nombreViejoLimpio !== $baseNuevoNombre) {
            
            // Buscar si existe un archivo físico con el nombre anterior
            $archivosEncontrados = glob($uploadFileDir . $nombreViejoLimpio . '.*');

            if ($archivosEncontrados && count($archivosEncontrados) > 0) {
                $rutaArchivoActual = $archivosEncontrados[0]; 
                $fileExtension = strtolower(pathinfo($rutaArchivoActual, PATHINFO_EXTENSION));
                
                // Definimos la nueva ruta que le corresponde con el nuevo nombre de usuario
                $rutaNuevaEsperada = $uploadFileDir . $baseNuevoNombre . '.' . $fileExtension;

                // Renombrar el archivo localmente en el disco
                rename($rutaArchivoActual, $rutaNuevaEsperada);
            }
        }
    }

    // --- ACTUALIZACIÓN EN LA BASE DE DATOS ---
    // Construcción del query dinámico según si se cambia o no la contraseña
    if (!empty($contraseña_usuario)) {
        // Si escribió una contraseña nueva, se incluye en el UPDATE
        $query_update = "UPDATE usuario SET 
                         nombre_usuario = '$nombre_usuario', 
                         contraseña_usuario = '$contraseña_usuario', 
                         nombre_completo = '$nombre_completo', 
                         rol = '$rol' 
                         WHERE id_usuario = $id_usuario";
    } else {
        // Si la dejó vacía, se omiten los cambios en la columna 'contraseña_usuario'
        $query_update = "UPDATE usuario SET 
                         nombre_usuario = '$nombre_usuario', 
                         nombre_completo = '$nombre_completo', 
                         rol = '$rol' 
                         WHERE id_usuario = $id_usuario";
    }

    // Ejecutar consulta
    if (mysqli_query($conexion, $query_update)) {
        echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar base de datos: ' . mysqli_error($conexion)]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de petición no permitido.']);
}

mysqli_close($conexion);
exit;
?>