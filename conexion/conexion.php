<?php
$servername = "localhost";
$username = "NOVA_CS"; // O tu usuario de MySQL
$password = "NCS123"; // Aquí va la contraseña que acabas de poner
$database = "nova_concept_store";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Validar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
echo "Conexión exitosa";
?>