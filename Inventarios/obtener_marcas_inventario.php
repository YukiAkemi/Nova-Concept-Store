<?php
// Configuración de cabeceras para permitir respuestas JSON
header('Content-Type: application/json; charset=utf-8');

// 1. CONFIGURACIÓN DE LA CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; // <--- Asegúrate de que este sea el nombre real de tu base de datos

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    echo json_encode(["error" => "Error de conexión: " . mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($conexion, "utf8");

// 2. CONSULTA SQL OPTIMIZADA
// Usamos IFNULL para asegurar que si el conteo es nulo o vacío, devuelva un 0 explícito.
$query = "SELECT 
            e.id_emprendedor, 
            e.nombre_emprendimiento, 
            IFNULL(COUNT(p.id_producto), 0) AS total_productos
          FROM emprendedores e
          LEFT JOIN producto p ON e.id_emprendedor = p.id_emprendedor
          GROUP BY e.id_emprendedor, e.nombre_emprendimiento";

$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    echo json_encode(["error" => "Error en la consulta: " . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit;
}

// 3. PROCESAMIENTO DE RESULTADOS
$marcas = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $fila['total_productos'] = (int)$fila['total_productos'];
    $marcas[] = $fila;
}

// 4. RETORNAR JSON
echo json_encode($marcas);

mysqli_close($conexion);
?>