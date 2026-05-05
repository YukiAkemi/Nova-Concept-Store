<?php
header('Content-Type: application/json');
error_reporting(0);

$conn = new mysqli("localhost", "root", "", "nova_concept_store");

$accion = $_GET['accion'] ?? '';

if ($accion == 'guardar') {
    $data = json_decode(file_get_contents('php://input'), true);
    foreach ($data['productos'] as $p) {
        $stmt = $conn->prepare("INSERT INTO apartados (cliente, producto, precio) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $data['cliente'], $p['nombre'], $p['precio']);
        $stmt->execute();
    }
    echo json_encode(["status" => "success"]);

} elseif ($accion == 'leer') {
    $result = $conn->query("SELECT * FROM apartados ORDER BY fecha DESC");
    $items = [];
    while($row = $result->fetch_assoc()) { $items[] = $row; }
    echo json_encode($items);

} elseif ($accion == 'borrar') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare("DELETE FROM apartados WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    echo json_encode(["status" => "success"]);
}

$conn->close();
?>