<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. CONFIGURACIÓN DE LA CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; 

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión interno: ' . mysqli_connect_error()]);
    exit;
}
mysqli_set_charset($conexion, "utf8");

// Función para limpiar el nombre del archivo (evita espacios, acentos y mayúsculas)
function limpiarNombreArchivo($texto) {
    $buscar     = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', ' ');
    $reemplazar = array('a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'n', 'n', '_');
    $limpio = str_replace($buscar, $reemplazar, $texto);
    return strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', $limpio));
}

// 2. PROCESAR LA ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_emprendedor = isset($_POST['id_emprendedor']) ? intval($_POST['id_emprendedor']) : 0;
    $nombre_emprendimiento = isset($_POST['nombre_emprendimiento']) ? mysqli_real_escape_string($conexion, trim($_POST['nombre_emprendimiento'])) : '';
    $nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conexion, trim($_POST['nombre'])) : '';
    $telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($conexion, trim($_POST['telefono'])) : '';
    $correo = isset($_POST['correo']) ? mysqli_real_escape_string($conexion, trim($_POST['correo'])) : '';

    if ($id_emprendedor === 0 || empty($nombre_emprendimiento) || empty($nombre) || empty($telefono) || empty($correo)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        mysqli_close($conexion);
        exit;
    }

    // Carpeta local de almacenamiento físico
    $uploadFileDir = '../Emprendedores/foto_emprendedores/'; 
    $baseNuevoNombre = limpiarNombreArchivo($_POST['nombre_emprendimiento']);

    // OBTENER EL NOMBRE VIEJO DEL EMPRENDIMIENTO (Para rastrear el archivo físico actual en la carpeta)
    $query_viejo = "SELECT nombre_emprendimiento FROM emprendedores WHERE id_emprendedor = $id_emprendedor";
    $res_viejo = mysqli_query($conexion, $query_viejo);
    $data_vieja = mysqli_fetch_assoc($res_viejo);
    
    $nombreViejoLimpio = $data_vieja ? limpiarNombreArchivo($data_vieja['nombre_emprendimiento']) : '';

    // --- MANEJO FÍSICO DE LA IMAGEN EN EL SERVIDOR ---
    
    // CASO A: EL USUARIO SÍ SUBIÓ UNA FOTO NUEVA
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
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
            move_uploaded_file($fileTmpPath, $dest_path);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido. Solo se actualizarán los campos de texto.']);
        }
        
    } else {
        // CASO B: NO SUBIÓ FOTO NUEVA (Solo renombramos el archivo existente si el nombre cambió)
        if (!empty($nombreViejoLimpio) && $nombreViejoLimpio !== $baseNuevoNombre) {
            
            // Buscar si existe un archivo físico con el nombre anterior
            $archivosEncontrados = glob($uploadFileDir . $nombreViejoLimpio . '.*');

            if ($archivosEncontrados && count($archivosEncontrados) > 0) {
                $rutaArchivoActual = $archivosEncontrados[0]; 
                $fileExtension = strtolower(pathinfo($rutaArchivoActual, PATHINFO_EXTENSION));
                
                // Definimos la nueva ruta que le corresponde con el nuevo nombre
                $rutaNuevaEsperada = $uploadFileDir . $baseNuevoNombre . '.' . $fileExtension;

                // Renombrar el archivo localmente en el disco
                rename($rutaArchivoActual, $rutaNuevaEsperada);
            }
        }
    }

    // --- ACTUALIZACIÓN DE TEXTOS EN LA BASE DE DATOS ---
    $query_update = "UPDATE emprendedores SET 
                     nombre_emprendimiento = '$nombre_emprendimiento', 
                     nombre = '$nombre', 
                     telefono = '$telefono', 
                     correo = '$correo' 
                     WHERE id_emprendedor = $id_emprendedor";
                    
    if (mysqli_query($conexion, $query_update)) {
        echo json_encode(['status' => 'success', 'message' => '¡Datos actualizados e imágenes gestionadas en el servidor correctamente!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar base de datos: ' . mysqli_error($conexion)]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de petición no válido.']);
}

mysqli_close($conexion);
?>