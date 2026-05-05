<?php
// Evita que cualquier advertencia ensucie la respuesta JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "nova_concept_store";

try {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception("Datos no recibidos");
    }

    $total = $data['total'];
    $descuento = $data['descuento'];

    $conn->begin_transaction();

    // Insertar Venta
    $stmt = $conn->prepare("INSERT INTO ventas (fecha, total, descuento) VALUES (NOW(), ?, ?)");
    $stmt->bind_param("dd", $total, $descuento);
    if (!$stmt->execute()) throw new Exception("Error en tabla ventas");
    
    $venta_id = $conn->insert_id;

    // Insertar Detalles
    $stmtD = $conn->prepare("INSERT INTO detalle_ventas (venta_id, producto, precio) VALUES (?, ?, ?)");
    foreach ($data['productos'] as $prod) {
        $nombre = $prod['nombre'];
        $precio = $prod['precio'];
        $stmtD->bind_param("isd", $venta_id, $nombre, $precio);
        $stmtD->execute();
    }

    $conn->commit();
    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    if (isset($conn) && $conn->ping()) $conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>