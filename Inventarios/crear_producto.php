<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto - Nova Concept Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Esteticos/estilos_dashboard.css">
    
    <style>
        :root {
            --rosa-principal: #E8B4BC;
            --rosa-oscuro: #C4969F;
            --texto-suave: #666;
        }

        .main-content {
            padding: 40px;
            background-color: #f8f9fa;
        }

        .contenido-formulario {
            background-color: #ffffff;
            border-radius: 15px;
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .header-formulario {
            border-bottom: 2px solid var(--rosa-principal);
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .header-formulario h2 {
            color: #333;
            font-size: 1.6rem;
            margin-bottom: 5px;
        }

        /* --- ESTILOS DE LOS CAMPOS DEL FORMULARIO --- */
        .grupo-control {
            margin-bottom: 20px;
            text-align: left;
        }

        .grupo-control label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
            font-size: 14px;
        }

        .grupo-control input[type="text"],
        .grupo-control input[type="number"],
        .grupo-control textarea,
        .grupo-control select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            color: #333;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .grupo-control input:focus,
        .grupo-control textarea:focus,
        .grupo-control select:focus {
            outline: none;
            border-color: var(--rosa-oscuro);
            box-shadow: 0 0 5px rgba(196, 150, 159, 0.2);
        }

        .grupo-control textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* --- BOTONES --- */
        .bloque-botones {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            border: none;
        }

        .btn-guardar {
            background-color: var(--rosa-principal);
            color: white;
        }

        .btn-guardar:hover {
            background-color: var(--rosa-oscuro);
        }

        .btn-cancelar {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .btn-cancelar:hover {
            background-color: #d6d8db;
        }

        /* --- AVATAR SIDEBAR --- */
        .avatar {
            width: 45px;
            height: 45px;
            background-color: #c49fa7;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 600;
            font-size: 18px;
            overflow: hidden;
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">            
            <div class="logo-container">
                <img src="../Esteticos/logo.png" alt="Logo Nova">
            </div>
            <div class="user-profile">
                <div class="avatar" id="avatar-contenedor">
                    <span id="letra-avatar">U</span>
                </div>
                <div class="user-info">
                    <p class="user-name" id="nombre-perfil">Cargando...</p>
                    <p class="user-role">ADMINISTRADOR</p>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="../ventas/Ventas.html" class="nav-item">
                    <span class="icon">💰 Ventas</span>
                </a>
                <a href="../ver ventas/ver_ventas.html" class="nav-item">
                    <span class="icon">📋 Historial de Ventas</span>
                </a>
                <a href="inventarios.html" class="nav-item">
                    <span class="icon">📦 Inventarios</span>
                </a>
                <a href="../Emprendedores/Emprendedores.html" class="nav-item">
                    <span class="icon">🚀 Emprendedores</span>
                </a>
                <a href="../Usuario/Usuarios.html" class="nav-item">
                    <span class="icon">👤 Usuarios</span>
                </a>
            </nav>
            <div class="logout-container">
                <a href="../index.html" class="btn-logout" onclick="localStorage.clear();" style="text-decoration: none;">
                    <span class="icon">🚪 Salir</span> 
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="contenido-formulario">
                <div class="header-formulario">
                    <h2><i class="fas fa-plus-circle"></i> Registrar Nuevo Producto</h2>
                    <p id="subtitulo-marca" style="color: var(--texto-suave); font-size: 14px;">Asignando producto al emprendedor...</p>
                </div>

                <form id="form-nuevo-producto" action="guardar_producto.php" method="POST">
                    <input type="hidden" name="id_emprendedor" id="id_emprendedor_input">

                    <div class="grupo-control">
                        <label for="nombre">Nombre del Producto</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Café Orgánico Molido" required>
                    </div>

                    <div class="grupo-control">
                        <label for="id_categoria">Categoría</label>
                        <select id="id_categoria" name="id_categoria" required>
                            <option value="">Selecciona una categoría</option>
                            <?php
                            // Conexión a la base de datos para extraer los datos reales
                            $conexion_select = mysqli_connect("localhost", "NOVA_CS", "NCS123", "nova_concept_store");
                            
                            if ($conexion_select) {
                                mysqli_set_charset($conexion_select, "utf8");
                                
                                // Consulta exacta adaptada al nombre de tu columna
                                $query_cat = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre ASC";
                                $resultado_cat = mysqli_query($conexion_select, $query_cat);
                                
                                while ($row_cat = mysqli_fetch_assoc($resultado_cat)) {
                                    echo '<option value="' . $row_cat['id_categoria'] . '">' . htmlspecialchars($row_cat['nombre']) . '</option>';
                                }
                                
                                mysqli_close($conexion_select);
                            } else {
                                echo '<option value="">Error al cargar categorías desde la BD</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="grupo-control">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" placeholder="Escribe las características o presentación del producto..." required></textarea>
                    </div>

                    <div class="grupo-control">
                        <label for="precio_unitario">Precio Unitario ($)</label>
                        <input type="number" id="precio_unitario" name="precio_unitario" step="0.01" min="0" placeholder="0.00" required>
                    </div>

                    <div class="bloque-botones">
                        <a href="inventarios.html" id="btn-cancelar" class="btn btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn btn-guardar">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. CAPTURAR LA ID DESDE LA URL (ej. crear_producto.php?id_emprendedor=7)
            const params = new URLSearchParams(window.location.search);
            const idEmprendedor = params.get('id_emprendedor');

            if (!idEmprendedor) {
                alert("Error: No se detectó la ID del emprendedor. Volviendo al menú principal.");
                window.location.href = "inventarios.html";
                return;
            }

            // Inyectar el ID en el input oculto para que PHP lo reciba
            document.getElementById('id_emprendedor_input').value = idEmprendedor;
            document.getElementById('subtitulo-marca').textContent = `ID de Emprendedor Destino: ${idEmprendedor}`;
            
            // Configurar dinámicamente el botón cancelar para regresar a la lista de este emprendedor
            document.getElementById('btn-cancelar').href = `detalle_inventario.html?id=${idEmprendedor}`;
        });
    </script>

    <script>
        // CONTROL INTEGRAL DEL PERFIL DE USUARIO EN EL SIDEBAR
        document.addEventListener("DOMContentLoaded", function() {
            const nombre = localStorage.getItem("usuarioLogueado");
            const cargo = localStorage.getItem("cargoUsuario");

            const elementoNombre = document.getElementById("nombre-perfil");
            const elementoCargo = document.querySelector(".user-role");
            const avatarContenedor = document.getElementById("avatar-contenedor");

            if (elementoNombre) elementoNombre.textContent = nombre || "Invitado";
            if (elementoCargo) elementoCargo.textContent = (cargo || "SIN CARGO").toUpperCase();

            if (nombre && nombre.trim() !== "") {
                avatarContenedor.innerHTML = `
                    <img src="../Usuario/foto/${nombre}.jpg" 
                         onerror="this.src='../Usuario/foto/${nombre}.png'; 
                                  this.onerror=function(){ this.src='../Usuario/foto/${nombre}.webp'; 
                                  this.onerror=function(){ this.src='../Usuario/foto/${nombre}.gif'; 
                                  this.onerror=function(){ this.src='../Usuario/foto/${nombre}.jpeg'; 
                                  this.onerror=function(){ this.parentElement.innerHTML='${nombre.charAt(0).toUpperCase()}'; }; }; }; };" 
                         alt="Avatar">`;
            } else {
                avatarContenedor.innerHTML = `<span id="letra-avatar">U</span>`;
            }
        });
    </script>
</body>
</html>