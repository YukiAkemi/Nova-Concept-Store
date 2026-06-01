<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

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

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception("Datos no recibidos o JSON inválido");
    }

    $total         = $data['total'];
    $subtotal      = $data['subtotal'];
    $descuento     = $data['descuento'];
    $tipo_pago     = $data['tipo_pago'];     
    $metodo_pago   = $data['metodo_pago'];   
    $comprobante   = $data['comprobante'];   
    $id_cliente    = $data['id_cliente'];    
    
    $id_usuario    = (!empty($data['id_usuario']) && $data['id_usuario'] > 0) ? intval($data['id_usuario']) : 1;    
    $id_cliente_final = (!empty($id_cliente) && $id_cliente > 0) ? intval($id_cliente) : 1;

    // --- MODIFICACIÓN DE FUERZA BRUTA (MODO DESARROLLO) ---
    // Desactivamos la verificación de llaves foráneas en la sesión actual
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    $conn->begin_transaction();

    // 1. Insertar en la tabla maestra 'venta'
    $sql_venta = "INSERT INTO venta (fecha, total, subtotal, descuento, tipo_pago, metodo_pago, comprobante, id_cliente, id_usuario) 
                  VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql_venta);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de ventas: " . $conn->error);
    }

    $stmt->bind_param("dddsssii", $total, $subtotal, $descuento, $tipo_pago, $metodo_pago, $comprobante, $id_cliente_final, $id_usuario);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al registrar en la tabla ventas: " . $stmt->error);
    }
    
    $venta_id = $conn->insert_id;

    // 2. Insertar en la tabla 'detalle_venta'
    $sql_detalle = "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, descuento_linea, precio_unitario) 
                    VALUES (?, ?, ?, ?, ?)";
                    
    $stmtD = $conn->prepare($sql_detalle);
    if (!$stmtD) {
        throw new Exception("Error al preparar la consulta de detalle: " . $conn->error);
    }

    foreach ($data['productos'] as $prod) {
        $id_producto     = $prod['id_producto'];
        $cantidad        = $prod['cantidad'];
        $descuento_linea = isset($prod['descuento_linea']) ? $prod['descuento_linea'] : 0; 
        $precio_unitario = $prod['precio_unitario'];

        $stmtD->bind_param("iiidd", $venta_id, $id_producto, $cantidad, $descuento_linea, $precio_unitario);
        
        if (!$stmtD->execute()) {
            throw new Exception("Error al insertar detalle del producto ID " . $id_producto . ": " . $stmtD->error);
        }
    }

    $conn->commit();

    // Volvemos a encender la verificación por seguridad global
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    echo json_encode(["status" => "success", "message" => "Venta registrada con éxito", "id_venta" => $venta_id], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->query("SET FOREIGN_KEY_CHECKS = 1"); // Asegurar encendido si falla
    }
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>