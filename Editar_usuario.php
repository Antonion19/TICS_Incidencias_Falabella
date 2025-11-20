<?php
include 'sesion.php';
include 'conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de empleado inválido.");
}

$empleado_id = (int) $_GET['id'];
$mensaje_error = "";

// 1. Obtener datos del empleado
$sql = "SELECT empleado_id, nombre, apellido, dni, correo, telefono, rol, area, cod_est_obj
        FROM empleado
        WHERE empleado_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en la consulta.");
}
$stmt->bind_param("i", $empleado_id);
$stmt->execute();
$result = $stmt->get_result();
$empleado = $result->fetch_assoc();
$stmt->close();

if (!$empleado) {
    die("Empleado no encontrado.");
}

// 2. Si envían el formulario, actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni      = trim($_POST['dni']);
    $correo   = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $rol      = trim($_POST['rol']);
    $area     = trim($_POST['area']);
    $estado   = trim($_POST['estado']); // activo / inactivo

    $cod_est_obj = ($estado === 'activo') ? 1 : 0;

    if ($nombre=='' || $apellido=='' || $dni=='' || $correo=='' || $telefono=='' || $rol=='' || $area=='') {
        $mensaje_error = "Todos los campos son obligatorios.";
    } else {
        $sqlUpdate = "UPDATE empleado
                      SET nombre = ?, apellido = ?, dni = ?, correo = ?, telefono = ?,
                          rol = ?, area = ?, cod_est_obj = ?
                      WHERE empleado_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        if ($stmt) {
            $stmt->bind_param(
                "sssssssii",
                $nombre,
                $apellido,
                $dni,
                $correo,
                $telefono,
                $rol,
                $area,
                $cod_est_obj,
                $empleado_id
            );
            if ($stmt->execute()) {
                // volver a la pantalla principal de gestión
                header("Location: GestionUsuario_Admin.php");
                exit;
            } else {
                $mensaje_error = "Error al actualizar el empleado.";
            }
            $stmt->close();
        } else {
            $mensaje_error = "Error en la preparación de la consulta.";
        }
    }
}

// estado actual (para marcar el select)
$estado_actual = ($empleado['cod_est_obj'] == 1) ? 'activo' : 'inactivo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Empleado</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="index.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <!-- SIDEBAR (igual que en tus otras páginas) -->
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

    <!-- BOTÓN FLOTANTE -->
    <button id="toggleSidebar" class="toggle-floating-btn">
        <i class="fa-solid fa-chevron-left"></i>
    </button>

    <main class="main">
        <div class="container-fluid px-4 py-4">
            <h2 class="fw-bold mb-3">Editar empleado</h2>

            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm p-4">
                <form method="POST">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control"
                                   value="<?php echo htmlspecialchars($empleado['apellido']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">DNI</label>
                            <input type="text" name="dni" class="form-control"
                                   value="<?php echo htmlspecialchars($empleado['dni']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control"
                                   value="<?php echo htmlspecialchars($empleado['correo']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                   value="<?php echo htmlspecialchars($empleado['telefono']); ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-select" required>
                                <option value="admin"    <?php echo $empleado['rol']==='admin'    ? 'selected' : ''; ?>>Administrador</option>
                                <option value="empleado" <?php echo $empleado['rol']==='empleado' ? 'selected' : ''; ?>>Empleado</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Área</label>
                            <select name="area" class="form-select" required>
                                <option value="ventas"  <?php echo $empleado['area']==='ventas'  ? 'selected' : ''; ?>>Ventas</option>
                                <option value="soporte" <?php echo $empleado['area']==='soporte' ? 'selected' : ''; ?>>Soporte</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="activo"   <?php echo $estado_actual==='activo'   ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo $estado_actual==='inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-3 mt-3">
                            <button type="submit" name="guardar_cambios" class="btn btn-warning">Guardar cambios</button>
                            <a href="GestionUsuario_Admin.php" class="btn btn-secondary">Cancelar</a>
