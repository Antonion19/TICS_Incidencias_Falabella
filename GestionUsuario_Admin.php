<?php
include 'sesion.php';
include 'conexion.php';

// --- MENSAJES ---
$mensaje_exito = '';
$mensaje_error = '';

// --- CREAR EMPLEADO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_empleado'])) {

    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni      = trim($_POST['dni']);
    $correo   = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $rol      = trim($_POST['rol']);
    $area     = trim($_POST['area']);
    $estado   = trim($_POST['estado']); 

    // Estado lógico
    $cod_est_obj = ($estado === "activo") ? 1 : 0;

    // Validación
    if ($nombre=='' || $apellido=='' || $dni=='' || $correo=='' || $telefono=='' || $rol=='' || $area=='') {
        $mensaje_error = "Todos los campos son obligatorios.";
    } else {

        // ----------------------------------------
        // 1. INSERTAR EMPLEADO
        // ----------------------------------------
        $sql = "INSERT INTO empleado (nombre, apellido, dni, correo, telefono, rol, area, cod_est_obj) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", 
            $nombre, 
            $apellido, 
            $dni, 
            $correo, 
            $telefono, 
            $rol, 
            $area, 
            $cod_est_obj
        );

        if ($stmt->execute()) {

            // Tomar el ID del empleado creado
            $empleado_id = $stmt->insert_id;

            // ----------------------------------------
            // 2. CREAR USUARIO AUTOMÁTICO
            // ----------------------------------------

            // Formato: nombre.apellido.area
            $username = strtolower(
                str_replace(' ', '', $nombre) . "." . 
                str_replace(' ', '', $apellido) . "." . 
                str_replace(' ', '', $area)
            );

            // contraseña = DNI (sin encriptación)
            $password = $dni;

            $sqlUser = "INSERT INTO usuario (username, password_hash, password_salt, empleado_id, cod_est_obj)
                        VALUES (?, ?, NULL, ?, 1)";

            $stmtUser = $conn->prepare($sqlUser);
            $stmtUser->bind_param("ssi", $username, $password, $empleado_id);

            if ($stmtUser->execute()) {
                $mensaje_exito = "Empleado y usuario creados correctamente.  
                                  Usuario: $username 
                                  Contraseña: $password";
            } else {
                $mensaje_error = "Empleado creado, pero error al crear usuario.";
            }

            $stmtUser->close();

        } else {
            $mensaje_error = "Error al crear empleado.";
        }

        $stmt->close();
    }
}

    // --- LISTAR EMPLEADOS ---
    $empleados = [];

    $sqlLista = "
        SELECT 
            e.empleado_id,
            e.nombre,
            e.apellido,
            e.dni,
            e.correo,
            e.telefono,
            e.rol,
            e.area,
            e.cod_est_obj,
            u.username,
            u.password_hash AS password
        FROM empleado e
        LEFT JOIN usuario u ON e.empleado_id = u.empleado_id
        ORDER BY e.empleado_id DESC
    ";

    $result = $conn->query($sqlLista);

    while ($fila = $result->fetch_assoc()) {
        $empleados[] = $fila;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Gestión de Usuarios</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Tu CSS -->
  <link href="index.css" rel="stylesheet">
</head>
<body>

<div class="layout">

    <!-- PANEL VERDE (SIDEBAR) -->
    <aside id="sidebar" class="sidebar">
        <div class="d-flex align-items-center mb-4 px-1">
            <img src="img/logo.png" alt="Logo" class="me-1" style="width: 60px; height: 60px;">
            <div class="lh-sm">
                <strong>Sistema de Tickets</strong><br>
                <small>Saga Falabella</small>
            </div>
        </div>

        <nav class="menu">
            <a href="inicio_Admin.php" class="menu-item"><i class="fa-solid fa-home"></i><span class="text">Panel Principal</span></a>
            <a href="GestionUsuario_Admin.php" class="menu-item active"><i class="fa-solid fa-users"></i><span class="text">Gestión de Usuarios</span></a>
            <a href="#" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Lista de Incidentes</span></a>
            <a href="informes_admin.php" class="menu-item"><i class="fa-solid fa-chart-line"></i><span class="text">Informes y Gráficos</span></a>
            <a href="repo_sol.php" class="menu-item"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
            <a href="CrearIncidente_Empleado.php" class="menu-item"><i class="fa-solid fa-circle-plus"></i><span class="text">Crear Incidente</span></a>
        </nav>

        <div class="user">
            <i class="fa-solid fa-user"></i>
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($nombre_completo); ?></div>
                <small class="role"><?php echo htmlspecialchars($rol); ?></small>
            </div>
        </div>
    </aside>

    <!-- BOTÓN FLOTANTE PARA COLAPSAR EL PANEL VERDE -->
    <button id="toggleSidebar" class="toggle-floating-btn">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <!-- BOTÓN CERRAR SESIÓN -->
    <button class="btn btn-dark btn-sm position-fixed"
            onclick="location.href='index.php'"
            style="top: 10px; right: 10px; z-index: 999;">
        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
    </button>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <div class="container-fluid px-4 py-4">

            <h2 class="fw-bold mb-1">Gestión de Usuarios</h2>
            <p class="text-muted mb-4">Crear, editar o eliminar empleados del sistema</p>

            <!-- MENSAJES -->
            <?php if ($mensaje_exito): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>

    <!-- FORMULARIO CREAR EMPLEADO -->
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">Nuevo empleado</h5>

        <form method="POST">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">DNI</label>
                <input type="text" name="dni" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select" required>
                    <option value="admin">Administrador</option>
                    <option value="empleado">Empleado</option>
                    <option value="empleado">Soporte TI</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Área</label>
                <select name="area" class="form-select" required>
                    <option value="ventas">Ventas</option>
                    <option value="soporte">Soporte</option>
                    <option value="TI">TI</option>
                    <option value="Logistia">Logistica</option>
                    <option value="Recursos Humanos">Recursos Humanos</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select" required>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <div class="col-12 mt-3">
                <button type="submit" name="crear_empleado" class="btn" style="background:#bdd62f;font-weight:bold;">
                    Crear Usuario
                </button>
            </div>

        </div>
        </form>
    </div>

    <!-- LISTA DE EMPLEADOS -->
    <div class="card shadow-sm">
        <div class="card-header"><strong>Lista de empleados</strong></div>
        <div class="card-body">

            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre completo</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Área</th>
                        <th>Estado</th>
                        <th>Usuario</th>        <!-- NUEVA COLUMNA -->
                        <th>Contraseña</th>     <!-- NUEVA COLUMNA -->
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($empleados as $e): ?>
                    <tr>
                        <td><?php echo $e['empleado_id']; ?></td>
                        <td><?php echo $e['nombre']." ".$e['apellido']; ?></td>
                        <td><?php echo $e['dni']; ?></td>
                        <td><?php echo $e['correo']; ?></td>
                        <td><?php echo $e['telefono']; ?></td>
                        <td><?php echo ucfirst($e['rol']); ?></td>
                        <td><?php echo ucfirst($e['area']); ?></td>

                        <td>
                            <?php if ($e['cod_est_obj']==1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <!-- NUEVAS CELDAS -->
                        <td><?php echo $e['username']; ?></td>
                        <td><?php echo $e['password']; ?></td>

                        <td>
                            <a href="editar_usuario.php?id=<?php echo $e['empleado_id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_usuario.php?id=<?php echo $e['empleado_id']; ?>" 
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Seguro de eliminar?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>
    </div>


</div>

</main>
</div>

  <!-- Script mínimo y correcto para el toggle -->
  <script>
    const sidebar = document.getElementById("sidebar");
    const toggleSidebar = document.getElementById("toggleSidebar");
    const toggleIcon = toggleSidebar.querySelector("i");

    toggleSidebar.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");

        if (sidebar.classList.contains("collapsed")) {
            toggleIcon.classList.replace("fa-chevron-left", "fa-chevron-right");
        } else {
            toggleIcon.classList.replace("fa-chevron-right", "fa-chevron-left");
        }
    });
  </script>
</body>
</html>
