<?php
// ==========================================
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// ==========================================
$servername = "localhost";
$username = "NOVA_CS"; 
$password = "NCS123"; 
$database = "nova_concept_store";

// Crear la conexión (Usando el enfoque del segundo código)
$conn = new mysqli($servername, $username, $password, $database);

// Validar la conexión
if ($conn->connect_error) {
    // Si falla, enviamos el error en formato JSON para no romper la respuesta del frontend
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error de conexión: " . $conn->connect_error]);
    exit;
}

// ==========================================
// 2. CONSULTA Y SCRIPT
// ==========================================
header('Content-Type: application/json');

try {
    // Tu consulta original intacta
    $query = "SELECT id_emprendedor, nombre_emprendimiento, nombre FROM emprendedores ORDER BY nombre_emprendimiento ASC";
    
    // Ejecutamos la consulta usando la nueva conexión ($conn->query)
    $resultado = $conn->query($query);
    
    // Validamos si la estructura de la consulta tiene errores
    if (!$resultado) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $emprendedores = [];
    
    // Recorremos los datos con el método adaptado a la nueva conexión
    while ($fila = $resultado->fetch_assoc()) {
        $emprendedores[] = $fila;
    }
    
    // Devolvemos el JSON tal y como lo hacía tu primer código
    echo json_encode($emprendedores);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>