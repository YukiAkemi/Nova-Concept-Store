<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "nova_concept_store";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>