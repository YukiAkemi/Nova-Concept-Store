<?php
// Permitimos reportar errores temporalmente para saber exactamente qué falla si se cae
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$user = "NOVA_CS";
$pass = "NCS123"; 
$db   = "nova_concept_store";

try {
    // Crear la conexión nativa MySQLi
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    // Asegurar codificación UTF-8
    $conn->set_charset("utf8");

    // CONSULTA ESTÁNDAR: Modifica p.nombre o p.precio si en tu BD se llaman distinto
    $query = "SELECT 
                e.id_emprendedor, 
                e.nombre_emprendimiento AS nombre_emprendedor,
                p.id_producto,
                p.nombre AS nombre_producto,
                p.precio_unitario AS precio_producto
              FROM emprendedores e
              LEFT JOIN producto p ON e.id_emprendedor = p.id_emprendedor
              ORDER BY e.nombre ASC, p.nombre ASC";

    $resultado_consulta = $conn->query($query);

    if (!$resultado_consulta) {
        throw new Exception("Error en la consulta SQL: " . $conn->error);
    }

    $entrepreneurs = [];

    while ($fila = $resultado_consulta->fetch_assoc()) {
        $id_emp = $fila['id_emprendedor'];
        
        // Si el emprendedor no ha sido agregado al objeto, lo creamos
        if (!isset($entrepreneurs[$id_emp])) {
            $entrepreneurs[$id_emp] = [
                'id_emprendedor' => $id_emp,
                'nombre_emprendedor' => $fila['nombre_emprendedor'],
                'productos' => []
            ];
        }

        // Si tiene un producto asociado en el JOIN, lo empujamos a su lista
        if ($fila['id_producto'] !== null) {
            $entrepreneurs[$id_emp]['productos'][] = [
                'id_producto' => (int)$fila['id_producto'],
                'nombre_producto' => $fila['nombre_producto'],
                'precio' => (float)$fila['precio_producto']
            ];
        }
    }

    // Limpiamos los errores anteriores del búfer antes de mandar el JSON real
    ob_clean();
    echo json_encode(array_values($entrepreneurs), JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>