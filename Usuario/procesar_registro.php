<?php
// Configuración de tu DB
$host = "localhost";
$user = "root";       // Cambia esto si tienes usuario
$pass = "";           // Cambia esto si tienes contraseña
$db   = "nova_concept_store"; // NOMBRE DE TU BASE DE DATOS

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Recibir datos
$nombre      = $_POST['nombre'];
$apellido    = $_POST['apellido'];
$nom_usuario = $_POST['nom_usuario'];
$contrasena  = $_POST['contrasena'];
$cargo       = $_POST['cargo'];

// Verificar si el usuario ya existe para no duplicar
$check = $conn->prepare("SELECT id FROM usuario WHERE Nom_Usuario = ?");
$check->bind_param("s", $nom_usuario);
$check->execute();
$resCheck = $check->get_result();

if ($resCheck->num_rows > 0) {
    echo "<span style='color:red;'>❌ El usuario ya existe</span>";
} else {
    // Insertar (Asegúrate que los nombres de columnas coincidan con tu imagen)
    $sql = "INSERT INTO usuario (Nombre, Apellido, Cargo, contraseña, Nom_Usuario) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido, $cargo, $contrasena, $nom_usuario);

    if ($stmt->execute()) {
        echo "<span style='color:green;'>✅ Registro exitoso</span>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
    $stmt->close();
}

$check->close();
$conn->close();
?>