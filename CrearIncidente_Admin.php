<?php include 'sesion.php';
// --- Lógica del formulario ---
$success = false;
$errors = [];

$title = '';
$description = '';
$category = '';
$priority = '';

// Traer categorías desde BD
$categorias = $conn->query("SELECT categoria_id, nombre FROM categoria_incidencia ORDER BY nombre ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? '';
    $priority    = $_POST['priority'] ?? '';

    if ($title === '' || $description === '' || $category === '' || $priority === '') {
        $errors[] = "Todos los campos son obligatorios.";
    } else {
        include 'conexion.php';

        // INSERT del incidente
        $stmt = $conn->prepare("
            INSERT INTO incidencia (titulo, descripcion, categoria_id, prioridad, reportado_por_emp, estado)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $estado = "Abierto"; // estado inicial siempre

        $stmt->bind_param("ssisss", 
            $title, 
            $description, 
            $category, 
            $priority, 
            $_SESSION['empleado_id'],
            $estado
        );

        $stmt->execute();

        $success = true;

        // Limpiar campos
        $title = $description = '';
        $category = $priority = '';
    }
}
?>
<!-- index.html -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Crear Incidente Usuario</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <!-- Tu CSS -->
  <link href="index.css" rel="stylesheet">
</head>
<body>

  <div class="layout">
    <!-- SIDEBAR -->
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
            <a href="GestionUsuario_Admin.php" class="menu-item"><i class="fa-solid fa-users"></i><span class="text">Gestión de Usuarios</span></a>
            <a href="lista_incidentes_Admin.php" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Lista de Incidentes</span></a>
            <a href="informes_admin.php" class="menu-item"><i class="fa-solid fa-chart-line"></i><span class="text">Informes y Gráficos</span></a>
            <a href="repo_sol_Admin.php" class="menu-item"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
            <a href="CrearIncidente_Admin.php" class="menu-item active"><i class="fa-solid fa-circle-plus"></i><span class="text">Crear Incidente</span></a>
        </nav>

        <div class="user">
            <i class="fa-solid fa-user"></i>
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($nombre_completo); ?></div>
                <small class="role"><?php echo htmlspecialchars($rol); ?></small>
            </div>
        </div>
    </aside>
    <!-- BOTÓN FLOTANTE PEGADO AL SIDEBAR -->
    <button id="toggleSidebar" class="toggle-floating-btn">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="btn btn-dark btn-sm position-fixed" onclick="location.href='index.php'" 
        style="top: 10px; right: 10px; z-index: 999;" >
        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
    </button>

    <!-- FORMULARIO -->
    <main class="main">
     <div class="container-fluid px-4 py-4">
        <h2 class="fw-bold mb-1">&nbsp&nbspCrear Nuevo Incidente</h2>
        <p class="text-muted mb-4">&nbsp&nbsp&nbsp Reporta un problema o solicita ayuda técnica</p>
        <!-- Mensajes -->
        <?php if ($success): ?>
          <div class="alert alert-success">
            ¡Incidente creado exitosamente! Tu ticket ha sido registrado.
          </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?>
              <div><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Tarjeta principal -->
        <div class="card mb-4">
          <div class="card-header">
            <strong>Información del Incidente</strong><br>
            <small class="text-muted">Completa el formulario con los detalles del problema</small>
          </div>
          <div class="card-body">
            <form method="POST" class="row g-3">
              <div class="col-12">
                <label for="title" class="form-label">Título del Incidente</label>
                <input
                  type="text"
                  class="form-control"
                  id="title"
                  name="title"
                  placeholder="Ej: Problema con el sistema de punto de venta"
                  value="<?= htmlspecialchars($title) ?>"
                  required >
                <div class="form-text">Resumen breve del problema</div>
              </div>

              <div class="col-12">
                <label for="description" class="form-label">Descripción Detallada</label>
                <textarea
                  class="form-control"
                  id="description"
                  name="description"
                  rows="4"
                  placeholder="Describe el problema con el mayor detalle posible..."
                  required
                ><?= htmlspecialchars($description) ?></textarea>
                <div class="form-text">Incluye pasos para reproducir el problema, mensajes de error, etc.</div>
              </div>

              <div class="col-md-6">
                <label for="category" class="form-label">Categoría</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Selecciona una categoría</option>
                    <?php while ($cat = $categorias->fetch_assoc()): ?>
                        <option value="<?= $cat['categoria_id'] ?>"
                            <?= ($category == $cat['categoria_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="priority" class="form-label">Prioridad</label>
                <select class="form-select" id="priority" name="priority" required>
                    <option value="">Selecciona la prioridad</option>
                    <option value="Alta" <?= $priority==='Alta'?'selected':'' ?>>Alta</option>
                    <option value="Media" <?= $priority==='Media'?'selected':'' ?>>Media</option>
                    <option value="Baja" <?= $priority==='Baja'?'selected':'' ?>>Baja</option>
                    <option value="Urgente" <?= $priority==='Urgente'?'selected':'' ?>>Urgente</option>
                </select>
              </div>

              <div class="col-12 d-flex gap-3">
                <button type="submit" class="btn px-4" style="background-color:#bdd62f; color:black; font-weight:600;">Crear Incidente</button>
                <button type="reset" class="btn btn-outline-secondary px-4">Limpiar Formulario</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Tarjeta de consejos -->
        <div class="card">
          <div class="card-header">
            <strong>Consejos para Reportar Incidentes</strong>
          </div>
          <div class="card-body">
            <ul class="mb-0">
              <li>Proporciona información específica sobre el problema.</li>
              <li>Menciona qué has intentado para resolver el problema.</li>
              <li>Indica si el problema afecta a otros usuarios.</li>
              <li>Selecciona la prioridad apropiada según el impacto.</li>
            </ul>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Script mínimo y correcto para el toggle (sin ids rotos) -->
  <script>
    const sidebar = document.getElementById("sidebar");
    const toggleSidebar = document.getElementById("toggleSidebar");
    const toggleIcon = toggleSidebar.querySelector("i");

    toggleSidebar.addEventListener("click", () => {

        sidebar.classList.toggle("collapsed");

        // Cambiar dirección de la flecha
        if (sidebar.classList.contains("collapsed")) {
            toggleIcon.classList.replace("fa-chevron-left", "fa-chevron-right");
        } else {
            toggleIcon.classList.replace("fa-chevron-right", "fa-chevron-left");
        }
    });
    </script>
</body>
</html>
