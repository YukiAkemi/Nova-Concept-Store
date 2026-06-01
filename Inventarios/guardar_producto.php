<?php
// 1. CONFIGURACIÓN DE CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; 

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    die("Error crítico de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

// 2. COMPROBAR QUE LOS DATOS LLEGUEN POR POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar que los IDs clave vengan en el POST y no estén vacíos
    if (!isset($_POST['id_categoria']) || empty($_POST['id_categoria'])) {
        die("Error: Debes seleccionar una categoría válida para el producto.");
    }
    if (!isset($_POST['id_emprendedor']) || empty($_POST['id_emprendedor'])) {
        die("Error: No se detectó un emprendedor válido asociado.");
    }

    // Escapar strings y castear datos
    $id_emprendedor  = (int)$_POST['id_emprendedor'];
    $nombre          = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $id_categoria    = (int)$_POST['id_categoria'];
    $descripcion     = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio_unitario = (float)$_POST['precio_unitario'];

    // 3. VALIDACIÓN EXTRAL: Verificar que el id_categoria realmente exista en la BD
    $check_categoria = "SELECT id_categoria FROM categoria WHERE id_categoria = $id_categoria";
    $resultado_check = mysqli_query($conexion, $check_categoria);

    if (mysqli_num_rows($resultado_check) === 0) {
        // Si entra aquí, significa que mandaste un ID (como 0) que no existe en la tabla categoria
        die("Error: La categoría con ID ($id_categoria) no existe en la base de datos. Por favor, verifica el formulario.");
    }

    // 4. INSERCIÓN SEGURA
    $query = "INSERT INTO producto (nombre, id_categoria, descripcion, precio_unitario, id_emprendedor) 
              VALUES ('$nombre', $id_categoria, '$descripcion', $precio_unitario, $id_emprendedor)";

    if (mysqli_query($conexion, $query)) {
        // Registro exitoso: Redirecciona al detalle de productos pasando el ID del emprendedor
        echo "<script>
                alert('¡Producto guardado exitosamente!');
                window.location.href = 'detalle_inventario.html?id=" . $id_emprendedor . "';
              </script>";
    } else {
        echo "Error al registrar el producto: " . mysqli_error($conexion);
    }
}

mysqli_close($conexion);
?>