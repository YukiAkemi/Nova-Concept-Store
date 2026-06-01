<?php
header('Content-Type: application/json');

// 1. Conexión a la base de datos (Ajusta con tus datos reales)
$servername = "localhost";
$username = "NOVA_CS";
$password = "NCS123";
$dbname = "nova_concept_store"; // Reemplaza con el nombre de tu base de datos

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
    exit;
}

// 2. Capturar datos del formulario de manera segura
$user_input = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$pass_input = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($user_input) || empty($pass_input)) {
    echo json_encode(["success" => false, "message" => "Campos vacíos"]);
    exit;
}

// 3. Consulta preparada para evitar Inyección SQL y buscar al usuario específico
$sql = "SELECT nombre_usuario, contraseña_usuario, rol FROM usuario WHERE nombre_usuario = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // 4. Validar la contraseña (Si usas texto plano compara directo, si usas password_hash usa password_verify)
    if ($pass_input === $row['contraseña_usuario']) {
        
        // Enviamos los datos dinámicos de la fila encontrada de vuelta al navegador
        echo json_encode([
            "success" => true,
            "nombre" => $row['nombre_usuario'],
            "rol" => $row['rol']
        ]);
        
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "El usuario no existe"]);
}

$stmt->close();
$conn->close();
?>