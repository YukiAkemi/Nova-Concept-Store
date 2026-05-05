<?php
// =========================================================
//  ALTA DE USUARIOS (Versión Ajustada a tu BD)
// =========================================================

include "conexion.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos del formulario (Usando los nombres de tu tabla)
    $nombre      = $_POST["nombre"];
    $apellido    = $_POST["apellido"];
    $cargo       = $_POST["cargo"];
    $contrasena  = $_POST["contrasena"];
    $nom_usuario = $_POST["nom_usuario"];

    // 2. SQL Ajustado: (El ID es autoincrementable, no se incluye)
    $sql = "INSERT INTO usuarios (Nombre, Apellido, Cargo, contraseña, Nom_Usuario)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);

    // 3. Ligar parámetros (5 strings)
    $stmt->bind_param("sssss", $nombre, $apellido, $cargo, $contrasena, $nom_usuario);

    if ($stmt->execute()) {
        $mensaje = "✅ Usuario registrado correctamente.";
    } else {
        $mensaje = "❌ Error al registrar: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Usuarios - Nova Concept</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 450px; margin: 40px auto; background-color: #f4f4f9; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { background: #343a40; color: white; padding: 15px; border-radius: 5px; margin-top: 0; text-align: center; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #333; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { margin-top: 25px; width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; transition: 0.3s; }
        button:hover { background: #218838; }
        .mensaje { margin-bottom: 20px; padding: 10px; border-radius: 5px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<div class="card">
    <h2>Alta de Usuarios</h2>

    <?php if ($mensaje != ""): ?>
        <div class="mensaje" style="background: <?= strpos($mensaje, '✅') !== false ? '#d4edda' : '#f8d7da' ?>;">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" required placeholder="Ej: Damaris Lizeth">

        <label>Apellido:</label>
        <input type="text" name="apellido" required placeholder="Ej: Venegas Ochoa">

        <label>Nombre de Usuario (Login):</label>
        <input type="text" name="nom_usuario" required placeholder="Ej: LizethV">

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required>

        <label>Cargo / Rol:</label>
        <select name="cargo">
            <option value="Super Admin">Super Admin</option>
            <option value="Admin">Admin</option>
            <option value="Vendedor">Vendedor</option>
        </select>

        <button type="submit">Registrar Usuario</button>
    </form>
</div>

</body>
</html>