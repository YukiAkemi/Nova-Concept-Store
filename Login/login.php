<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
error_reporting(E_ALL); // Cambia temporalmente a E_ALL para ver si hay errores ocultos
ini_set('display_errors', 0);

include "../conexion/conexion.php";

// 1. Asegurar charset
$conn->set_charset("utf8mb4");

$user = trim($_POST["usuario"] ?? '');
$password = trim($_POST["contrasena"] ?? '');

if (empty($user) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

try {
    // 2. Consulta verificando que los nombres de columna coincidan exactamente
    $stmt = $conn->prepare("SELECT Nom_Usuario, Cargo FROM usuario WHERE Nom_Usuario = ? AND contraseña = ?");
    $stmt->bind_param("ss", $user, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        
        $_SESSION["nombre_usuario"] = $userData["Nom_Usuario"];
        $_SESSION["cargo"] = $userData["Cargo"];

        echo json_encode([
            "success" => true,
            "cargo" => $userData["Cargo"],
            "usuario" => $userData["Nom_Usuario"]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}

if(isset($stmt)) $stmt->close();
// IMPORTANTE: Si es una API JSON, no debe haber HTML después de este punto.
exit; 
?>