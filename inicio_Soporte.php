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
        <a href="inicio_Soporte.php" class="menu-item active"><i class="fa-solid fa-home"></i><span class="text">Panel Principal</span></a>
        <a href="gestion_Incidentes_Soporte.php" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Gestión de Incidentes</span></a>
        <a href="repo_sol_Soporte.php" class="menu-item"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
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

      <!-- TÍTULO DE BIENVENIDA -->
      <div class="mb-4">
          <h2 class="fw-bold text-success">&nbsp&nbsp¡Bienvenido, <?php echo htmlspecialchars($nombre_completo); ?>!</h2>
          <p class="text-muted">&nbsp&nbspPanel principal del área de Soporte Técnico – Saga Falabella</p>
      </div>

      <!-- TARJETAS DE RESUMEN -->
      <div class="row g-4">

          <!-- Incidentes abiertos -->
          <div class="col-md-4">
              <div class="card shadow-sm border-0">
                  <div class="card-body d-flex align-items-center">
                      <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center me-3"
                          style="width:55px;height:55px;">
                          <i class="fa-solid fa-ticket"></i>
                      </div>
                      <div>
                          <h5 class="card-title mb-1">Incidentes Abiertos</h5>
                          <p class="card-text text-muted">Incidentes que requieren atención inmediata.</p>
                      </div>
                  </div>
              </div>
          </div>

          <!-- En proceso -->
          <div class="col-md-4">
              <div class="card shadow-sm border-0">
                  <div class="card-body d-flex align-items-center">
                      <div class="rounded-circle bg-warning text-white d-flex justify-content-center align-items-center me-3"
                          style="width:55px;height:55px;">
                          <i class="fa-solid fa-gears"></i>
                      </div>
                      <div>
                          <h5 class="card-title mb-1">En Proceso</h5>
                          <p class="card-text text-muted">Incidentes que están siendo gestionados.</p>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Repositorio de soluciones -->
          <div class="col-md-4">
              <div class="card shadow-sm border-0">
                  <div class="card-body d-flex align-items-center">
                      <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                          style="width:55px;height:55px;">
                          <i class="fa-solid fa-book"></i>
                      </div>
                      <div>
                          <h5 class="card-title mb-1">Repositorio</h5>
                          <p class="card-text text-muted">Acceso rápido a soluciones registradas.</p>
                      </div>
                  </div>
              </div>
          </div>

      </div>

      <!-- SECCIÓN INFORMATIVA -->
      <div class="mt-5">
          <div class="p-4 rounded shadow-sm border" style="background:#f9f9f9;">
              <h4 class="text-success fw-bold">¿Qué puedes hacer desde este panel?</h4>
              <ul class="mt-3 text-muted">
                  <li>Revisar y gestionar incidentes asignados.</li>
                  <li>Registrar avances y soluciones técnicas.</li>
                  <li>Consultar el repositorio de soluciones.</li>
                  <li>Acceder rápidamente a los módulos desde la barra lateral.</li>
              </ul>
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
