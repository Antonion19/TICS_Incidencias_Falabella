<?php include 'sesion.php'; ?>
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
        <a href="inicio_Empleado.php" class="menu-item active"><i class="fa-solid fa-home"></i><span class="text">Panel Principal</span></a>
        <a href="CrearIncidente_Empleado.php" class="menu-item"><i class="fa-solid fa-circle-plus"></i><span class="text">Crear Incidente</span></a>
        <a href="lista_incidentes_Empleado.php" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Mis Incidentes</span></a>
        <a href="repo_sol_Empleado.php" class="menu-item"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
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
    <main class="main p-4">

      <div class="container">

          <h2 class="fw-bold mt-2 mb-3">Panel de Empleado</h2>
          <p class="text-muted mb-4">Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?>.  
            Desde aquí puedes gestionar usuarios, supervisar incidencias y acceder a estadísticas principales.</p>



          <!-- Accesos rápidos -->
          <div class="row g-4">

              <div class="col-md-4">
                  <div class="card quick-card shadow-sm text-center">
                      <div class="card-body">
                          <i class="fa-solid fa-circle-plus fa-2x mb-3 text-success"></i>
                          <h5 class="fw-bold">Crear un Incidente</h5>
                          <p class="text-muted small">Reporta un problema y notifícalo al equipo de soporte.</p>
                          <a href="CrearIncidente_Empleado.php" class="btn btn-success btn-sm">
                              Crear incidente
                          </a>
                      </div>
                  </div>
              </div>

              <div class="col-md-4">
                  <div class="card quick-card shadow-sm text-center">
                      <div class="card-body">
                          <i class="fa-solid fa-list fa-2x mb-3 text-primary"></i>
                          <h5 class="fw-bold">Mis Incidentes</h5>
                          <p class="text-muted small">Consulta el progreso de los incidentes que has registrado.</p>
                          <a href="lista_incidentes_Empleado.php" class="btn btn-primary btn-sm">
                              Ver lista
                          </a>
                      </div>
                  </div>
              </div>

              <div class="col-md-4">
                  <div class="card quick-card shadow-sm text-center">
                      <div class="card-body">
                          <i class="fa-solid fa-book fa-2x mb-3 text-warning"></i>
                          <h5 class="fw-bold">Repositorio de Soluciones</h5>
                          <p class="text-muted small">Busca soluciones ya documentadas para problemas comunes.</p>
                          <a href="repo_sol_Empleado.php" class="btn btn-warning btn-sm text-white">
                              Ver repositorio
                          </a>
                      </div>
                  </div>
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
