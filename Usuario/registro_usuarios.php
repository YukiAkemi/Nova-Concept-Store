<?php
// 1. Conexión a la base de datos
include "conexion.php"; 

$mensaje = "";

// 2. Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos (nombres de variables coinciden con los 'name' del formulario)
    $nombre      = $_POST["nombre"];
    $apellido    = $_POST["apellido"];
    $cargo       = $_POST["cargo"];
    $contrasena  = $_POST["contrasena"];
    $nom_usuario = $_POST["nom_usuario"];

    // 3. SQL basado exactamente en tu imagen: (Id es autoincrementable)
    // Nota: 'contraseña' con 'ñ' debe escribirse igual que en tu BD
    $sql = "INSERT INTO usuario (Nombre, Apellido, Cargo, contraseña, Nom_Usuario) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conexion->prepare($sql)) {
        // "sssss" indica que los 5 campos son strings
        $stmt->bind_param("sssss", $nombre, $apellido, $cargo, $contrasena, $nom_usuario);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert success'>✅ Usuario registrado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>❌ Error al registrar: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios - Nova Concept</title>
    <style>
        /* Estilos usando la paleta de colores de tu CSS original */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fcf8f9; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            border-top: 10px solid #d6adba; /* Color rosa de tu login */
        }

        h2 {
            color: #777777;
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #eaeaea;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #d6adba;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background-color: #d6adba; /* Botón con tu paleta de colores */
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #c49aab; 
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <h2>Alta de Usuario</h2>

    <?php echo $mensaje; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Nombre:</label>
            <input type="text" name="nombre" required placeholder="Ej: Damaris Lizeth">
        </div>

        <div class="form-group">
            <label>Apellido:</label>
            <input type="text" name="apellido" required placeholder="Ej: Venegas Ochoa">
        </div>

        <div class="form-group">
            <label>Nombre de Usuario (Login):</label>
            <input type="text" name="nom_usuario" required placeholder="Ej: LizethV">
        </div>

        <div class="form-group">
            <label>Contraseña:</label>
            <input type="password" name="contrasena" required placeholder="Crea una contraseña">
        </div>

        <div class="form-group">
            <label>Cargo / Rol:</label>
            <select name="cargo">
                <option value="Super Admin">Super Admin</option>
                <option value="Admin">Admin</option>
                <option value="Vendedor">Vendedor</option>
            </select>
        </div>

        <button type="submit">Registrar Usuario</button>
    </form>
</div>

</body>
</html>