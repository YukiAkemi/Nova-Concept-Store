<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

$conn = new mysqli("localhost", "NOVA_CS", "NCS123", "nova_concept_store");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Conexión fallida"]);
    exit;
}

$accion = $_GET['accion'] ?? '';

if ($accion == 'guardar') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || empty($data['productos'])) {
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
        exit;
    }

    $cliente = $data['cliente'];
    $total = 0;
    foreach ($data['productos'] as $p) {
        $total += ($p['precio_unitario'] * $p['cantidad']);
    }

    $conn->begin_transaction();

    try {
        // 1. Registrar maestro
        $stmt = $conn->prepare("INSERT INTO apartados (cliente, total) VALUES (?, ?)");
        $stmt->bind_param("sd", $cliente, $total);
        if (!$stmt->execute()) throw new Exception("Error al guardar maestro de apartado");
        
        $id_apartado = $conn->insert_id;

        // 2. Registrar detalles incluyendo el NOMBRE del producto
        $stmtD = $conn->prepare("INSERT INTO detalle_apartados (id_apartado, id_producto, nombre_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($data['productos'] as $p) {
            $id_prod = $p['id_producto'];
            $nombre  = $p['nombre']; 
            $cant    = $p['cantidad'];
            $precio  = $p['precio_unitario'];
            
            $stmtD->bind_param("iisid", $id_apartado, $id_prod, $nombre, $cant, $precio);
            if (!$stmtD->execute()) throw new Exception("Error en producto ID: " . $id_prod);
        }

        $conn->commit();
        echo json_encode(["status" => "success"]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

} elseif ($accion == 'leer') {
    // Consulta con INNER JOIN para traer los productos y sus datos maestros
    $sql = "SELECT a.id_apartado, a.cliente, a.total, a.fecha, a.estado,
                   d.id_producto, d.nombre_producto, d.cantidad, d.precio_unitario 
            FROM apartados a
            INNER JOIN detalle_apartados d ON a.id_apartado = d.id_apartado
            WHERE a.estado = 'Pendiente'
            ORDER BY a.fecha DESC";
            
    $result = $conn->query($sql);
    
    // Agrupación inteligente por ID de Apartado
    $apartados_agrupados = [];
    
    while($row = $result->fetch_assoc()) { 
        $id_apartado = $row['id_apartado'];
        
        if (!isset($apartados_agrupados[$id_apartado])) {
            $apartados_agrupados[$id_apartado] = [
                "id_apartado" => $id_apartado,
                "cliente"     => $row['cliente'],
                "total"       => $row['total'],
                "fecha"       => $row['fecha'],
                "estado"      => $row['estado'],
                "productos"   => []
            ];
        }
        
        $apartados_agrupados[$id_apartado]['productos'][] = [
            "id_producto"     => $row['id_producto'],
            "nombre_producto" => $row['nombre_producto'],
            "cantidad"        => $row['cantidad'],
            "precio_unitario" => $row['precio_unitario']
        ];
    }
    
    // Se reindexa para enviar un array JSON limpio []
    echo json_encode(array_values($apartados_agrupados));

} elseif ($accion == 'borrar') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("DELETE FROM apartados WHERE id_apartado = ?");
    $stmt->bind_param("i", $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
}

$conn->close();
?>