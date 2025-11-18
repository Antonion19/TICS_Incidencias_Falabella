<?php include 'sesion.php'; 

$sql = "SELECT 
            i.incidencia_id,
            i.titulo,
            i.descripcion,
            c.nombre AS categoria,
            i.prioridad,
            i.estado,
            
            rep.nombre AS creado_nombre,
            rep.apellido AS creado_apellido,

            asg.nombre AS asignado_nombre,
            asg.apellido AS asignado_apellido,

            DATE(i.created_at) AS fecha_creado,
            DATE(i.fecha_cierre) AS fecha_cierre

        FROM incidencia i
        LEFT JOIN categoria_incidencia c 
            ON i.categoria_id = c.categoria_id

        LEFT JOIN empleado rep 
            ON i.reportado_por_emp = rep.empleado_id

        LEFT JOIN empleado asg 
            ON i.asignado_a_emp = asg.empleado_id

        WHERE i.cod_est_obj = 1
        ORDER BY i.incidencia_id DESC";

$incidentes = $conn->query($sql);

?>
<!-- index.html -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Sistema de Tickets - Layout</title>

  <!-- Bootstrap (solo CSS, no JS necesario para este layout) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome (iconos) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Tu CSS separado -->
  <link href="index.css" rel="stylesheet">

  <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
</head>
<body>

  <div class="layout">
    <!-- SIDEBAR -->
    <aside id="sidebar" class="sidebar">
      <!-- Nota: coloca aquí el archivo de logo (recomendado: imagen de 320x320 px). CSS lo mostrará en el tamaño adecuado. -->
      <!-- LOGO + TEXTO -->
        <div class="d-flex align-items-center mb-4 px-1">
            <img src="img/logo.png" alt="Logo" class="me-1" style="width: 60px; height: 60px;">
            
            <div class="lh-sm">
                <strong>Sistema de Tickets</strong><br>
                <small>Saga Falabella</small>
            </div>
        </div>

      <nav class="menu">
        <a href="inicio_Admin.php" class="menu-item active"><i class="fa-solid fa-home"></i><span class="text">Panel Principal</span></a>
        <a href="#" class="menu-item"><i class="fa-solid fa-users"></i><span class="text">Gestión de Usuarios</span></a>
        <a href="lista_incidentes_Admin.php" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Lista de Incidentes</span></a>
        <a href="informes_admin.php" class="menu-item"><i class="fa-solid fa-chart-line"></i><span class="text">Informes y Gráficos</span></a>
        <a href="repo_sol_Admin.php" class="menu-item"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
        <a href="CrearIncidente_Admin.php" class="menu-item"><i class="fa-solid fa-circle-plus"></i><span class="text">Crear Incidente</span></a>
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
    <!-- MAIN (vacío por ahora, lo veremos después) -->
    <main class="main">
        <div class="container-fluid px-4 py-4">

            <h2 class="fw-bold mb-1">&nbsp&nbspLista de Incidentes</h2>
            <p class="text-muted mb-4">&nbsp&nbsp&nbsp Vista general de todos los tickets del sistema</p>

            <!-- TABLA DE INCIDENTES -->
            <div class="table-responsive">
                <table id="tablaIncidentes" class="table table-striped table-bordered align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Creado Por</th>
                            <th>Asignado A</th>
                            <th>Fecha de Creación</th>
                            <th>Fecha de Cierre</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($incidentes->num_rows > 0): ?>
                            <?php while ($row = $incidentes->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['incidencia_id'] ?></td>

                                    <td><?= htmlspecialchars($row['titulo']) ?></td>

                                    <td><?= htmlspecialchars($row['descripcion']) ?></td>

                                    <td><?= htmlspecialchars($row['categoria']) ?></td>

                                    <td>
                                        <?php
                                            $p = strtolower($row['prioridad']);
                                            $color = "bg-secondary";

                                            if ($p === "crítica" || $p === "critica") $color = "bg-danger";
                                            else if ($p === "alta") $color = "bg-warning";
                                            else if ($p === "media") $color = "bg-info";
                                            else if ($p === "baja") $color = "bg-success";
                                        ?>
                                        <span class="badge <?= $color ?>"><?= htmlspecialchars($row['prioridad']) ?></span>
                                    </td>

                                    <td>
                                        <?php
                                            $e = strtolower($row['estado']);
                                            $colorE = "bg-secondary";

                                            if ($e === "cerrado") $colorE = "bg-danger";
                                            else if ($e === "en proceso") $colorE = "bg-primary";
                                            else if ($e === "abierto") $colorE = "bg-success";
                                        ?>
                                        <span class="badge <?= $colorE ?>"><?= htmlspecialchars($row['estado']) ?></span>
                                    </td>

                                    <td><?= htmlspecialchars($row['creado_nombre'] . " " . $row['creado_apellido']) ?></td>

                                    <td>
                                        <?= $row['asignado_nombre'] 
                                            ? htmlspecialchars($row['asignado_nombre'] . " " . $row['asignado_apellido']) 
                                            : "<i>No asignado</i>" ?>
                                    </td>

                                    <td><?= htmlspecialchars($row['fecha_creado']) ?></td>
                                    <td><?= htmlspecialchars($row['fecha_cierre']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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

    <script>
    $(document).ready(function () {
        $("#tablaIncidentes").DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            }
        });
    });
    </script>
</body>
</html>
