<?php
include 'conexion.php';
header('Content-Type: application/json');
ob_start();

$response = ["status" => "error", "message" => "Ocurrió un error inesperado"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización de datos
    $marca = mysqli_real_escape_string($conn, $_POST['nombre_marca']);
    $responsable = mysqli_real_escape_string($conn, $_POST['nombre_responsable']);
    $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    
    // Configuración de la foto
    $nombre_foto = "default.png"; // Valor por defecto si no suben nada

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $img_name = $_FILES['foto']['name'];
        $tmp_name = $_FILES['foto']['tmp_name'];
        
        // Obtener extensión y crear nombre único para evitar duplicados
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $nuevo_nombre = "img_" . uniqid() . "." . $ext;
        
        $carpeta_destino = "fotos_emprendedores/";
        $ruta_final = $carpeta_destino . $nuevo_nombre;

        // Crear la carpeta si no existe
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }

        // Mover el archivo de la memoria temporal a la carpeta real
        if (move_uploaded_file($tmp_name, $ruta_final)) {
            $nombre_foto = $nuevo_nombre;
        }
    }

    // Insertar en la base de datos (Asegúrate de haber corrido el ALTER TABLE)
    $sql = "INSERT INTO emprendedor (nombre_marca, nombre_responsable, telefono, categoria, foto) 
            VALUES ('$marca', '$responsable', '$telefono', '$categoria', '$nombre_foto')";

    if (mysqli_query($conn, $sql)) {
        $response["status"] = "success";
        $response["message"] = "Emprendedor registrado correctamente con su foto";
    } else {
        $response["message"] = "Error en la base de datos: " . mysqli_error($conn);
    }
}

ob_clean();
echo json_encode($response);
?>