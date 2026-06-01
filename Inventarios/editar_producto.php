<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. CONFIGURACIÓN DE LA CONEXIÓN
$servidor = "localhost";
$usuario  = "NOVA_CS";
$password = "NCS123";
$db       = "nova_concept_store"; 

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    die("Error de conexión interno: " . mysqli_connect_error());
}
mysqli_set_charset($conexion, "utf8");

// 2. VALIDAR LA ID RECIBIDA
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID de producto no proporcionada.");
}

$id_producto = (int)$_GET['id'];
$mensaje = "";

// 3. PROCESAR LA ACTUALIZACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $id_categoria = (int)$_POST['categoria']; // Recibe el ID seleccionado en el <select>

    $query_update = "UPDATE producto 
                     SET nombre = '$nombre', descripcion = '$descripcion', precio_unitario = $precio, id_categoria = $id_categoria 
                     WHERE id_producto = $id_producto";

    if (mysqli_query($conexion, $query_update)) {
        $mensaje = "<div style='color: green; font-weight: bold; background: #e6f4ea; padding: 12px; border-radius: 8px; margin-bottom:15px; text-align:center;'>¡Producto actualizado con éxito!</div>";
    } else {
        $mensaje = "<div style='color: red; font-weight: bold; background: #fce8e6; padding: 12px; border-radius: 8px; margin-bottom:15px; text-align:center;'>Error al actualizar: " . mysqli_error($conexion) . "</div>";
    }
}

// 4. OBTENER LOS DATOS ACTUALES DEL PRODUCTO (GET)
$query_select = "SELECT * FROM producto WHERE id_producto = $id_producto";
$resultado = mysqli_query($conexion, $query_select);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    die("Error: El producto solicitado no existe.");
}

// 5. OBTENER TODAS LAS CATEGORÍAS PARA EL MENÚ DESPLEGABLE
// Nota: Ajusta 'categoria', 'id_categoria' y 'nombre' si cambian en tu base de datos
$query_categorias = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC";
$result_categorias = mysqli_query($conexion, $query_categorias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - <?php echo htmlspecialchars($producto['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; background-color: #f8f9fa; padding: 40px; }
        .form-container { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { color: #333; margin-top: 0; border-bottom: 2px solid #E8B4BC; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; }
        .form-group label { font-weight: 600; margin-bottom: 8px; color: #555; }
        .form-group input, .form-group textarea, .form-group select { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; background-color: white; }
        .btn-guardar { background-color: #E8B4BC; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; transition: 0.3s; width: 100%; }
        .btn-guardar:hover { background-color: #C4969F; }
        .volver-link { display: inline-block; margin-top: 15px; text-decoration: none; color: #C4969F; font-weight: 600; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Editar Producto</h2>
    
    <?php echo $mensaje; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio ($)</label>
            <input type="number" step="0.01" id="precio" name="precio" value="<?php echo $producto['precio_unitario']; ?>" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php 
                if ($result_categorias && mysqli_num_rows($result_categorias) > 0) {
                    while ($cat = mysqli_fetch_assoc($result_categorias)) {
                        // Si la categoría coincide con la del producto, la dejamos marcada (selected)
                        $selected = ($cat['id_categoria'] == $producto['id_categoria']) ? "selected" : "";
                        echo "<option value='{$cat['id_categoria']}' $selected>" . htmlspecialchars($cat['nombre']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No se encontraron categorías disponibles</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
    </form>

    <a href="javascript:history.back()" class="volver-link"><i class="fas fa-chevron-left"></i> Volver al Inventario</a>
</div>

</body>
</html>
<?php mysqli_close($conexion); ?>