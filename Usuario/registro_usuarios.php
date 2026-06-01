<?php
// 1. Evitar que errores o advertencias de PHP ensucien la respuesta JSON
error_reporting(0); 
ini_set('display_errors', 0);

// 2. Incluir conexión (Se usará la variable que definas en tu archivo, ej: $conn o $conexion)
include '../conexion/conexion.php'; 

// Si tu archivo usa '$conn', descomenta la línea de abajo para evitar conflictos:
if (isset($conn) && !isset($conexion)) { $conexion = $conn; }

// 3. Configurar cabeceras para responder exclusivamente en formato JSON
header('Content-Type: application/json; charset=utf-8');
ob_clean(); 

$response = array();

// 4. Procesar la petición POST cuando se envía desde el JavaScript
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capturar los nuevos nombres de campo enviados por el formulario modificado
    $nombre_completo     = isset($_POST["nombre_completo"]) ? trim($_POST["nombre_completo"]) : '';
    $nombre_usuario      = isset($_POST["nombre_usuario"]) ? trim($_POST["nombre_usuario"]) : '';
    $contraseña_usuario  = isset($_POST["contraseña_usuario"]) ? $_POST["contraseña_usuario"] : '';
    $rol                 = isset($_POST["rol"]) ? $_POST["rol"] : '';

    // Validación simple de campos obligatorios
    if (empty($nombre_completo) || empty($nombre_usuario) || empty($contraseña_usuario) || empty($rol)) {
        $response["status"] = "error";
        $response["message"] = "Todos los campos son obligatorios.";
        echo json_encode($response);
        exit;
    }

    // 5. Sentencia SQL adaptada EXACTAMENTE a las columnas de la imagen:
    // nombre_usuario | contraseña_usuario | nombre_completo | rol
    $sql = "INSERT INTO usuario (nombre_usuario, contraseña_usuario, nombre_completo, rol) VALUES (?, ?, ?, ?)";

    if ($stmt = $conexion->prepare($sql)) {
        // "ssss" mapea los 4 campos como cadenas de texto (strings)
        $stmt->bind_param("ssss", $nombre_usuario, $contraseña_usuario, $nombre_completo, $rol);

        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "Usuario registrado correctamente.";
        } else {
            $response["status"] = "error";
            $response["message"] = "Error al ejecutar en la Base de Datos: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response["status"] = "error";
        $response["message"] = "Error en la preparación de la consulta SQL: " . $conexion->error;
    }
} else {
    $response["status"] = "error";
    $response["message"] = "Método de petición no permitido.";
}

// 6. Enviar respuesta final estructurada al JavaScript y finalizar ejecución
echo json_encode($response);
exit;
?>