<?php
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "NOVA_CS";
$pass = "NCS123"; 
$db   = "nova_concept_store";

try {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");

} catch(Exception $exception) {
    echo json_encode(["error" => "Error de conexión: " . $exception->getMessage()]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';

// ACCIÓN 1: Listar todas las ventas (Historial)
if ($accion === 'listar') {
    $query = "SELECT id_venta, fecha, total, subtotal, descuento, tipo_pago, metodo_pago, comprobante, id_cliente, id_usuario 
              FROM venta ORDER BY fecha DESC";
              
    $resultado = $conn->query($query);
    if (!$resultado) {
        echo json_encode(["error" => "Error en la consulta: " . $conn->error]);
        exit;
    }
    echo json_encode($resultado->fetch_all(MYSQLI_ASSOC));

// ACCIÓN 2: Obtener datos generales de UNA sola venta específica
} else if ($accion === 'obtener_venta' && isset($_GET['id_venta'])) {
    $id_venta = intval($_GET['id_venta']);
    $query = "SELECT id_venta, fecha, total, subtotal, descuento, tipo_pago, metodo_pago, comprobante, id_cliente, id_usuario 
              FROM venta WHERE id_venta = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Error al preparar: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    echo json_encode($resultado->fetch_assoc()); // Retorna un solo objeto directamente
    $stmt->close();

// ACCIÓN 3: Obtener los artículos/detalles vinculados a la venta (Con INNER JOIN para los nombres)
} else if ($accion === 'detalle' && isset($_GET['id_venta'])) {
    $id_venta = intval($_GET['id_venta']);
    
    $query = "SELECT dv.id_venta, dv.id_producto, dv.cantidad, dv.descuento_linea, dv.precio_unitario, p.nombre AS nombre_producto
              FROM detalle_venta dv
              INNER JOIN producto p ON dv.id_producto = p.id_producto
              WHERE dv.id_venta = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Error al preparar detalle: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $resultado = $stmt->get_result();
    echo json_encode($resultado->fetch_all(MYSQLI_ASSOC));
    $stmt->close();
}

$conn->close();
?>