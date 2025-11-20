<?php include 'sesion.php'; ?>
<?php
// Obtener todas las soluciones activas
$sql = "SELECT 
            r.titulo,
            r.detalle_solucion,
            r.palabras_clave,
            c.nombre AS categoria_nombre,
            c.categoria_id
        FROM repositorio_soluciones r
        INNER JOIN incidencia i ON r.incidencia_id = i.incidencia_id
        INNER JOIN categoria_incidencia c ON i.categoria_id = c.categoria_id
        WHERE r.cod_est_obj = 1
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);

// Obtener categorías activas del sistema
$sqlCategorias = "SELECT categoria_id, nombre 
                  FROM categoria_incidencia
                  WHERE cod_est_obj = 1
                  ORDER BY nombre ASC";

$categorias = $conn->query($sqlCategorias);
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
        <a href="inicio_Soporte.php" class="menu-item"><i class="fa-solid fa-home"></i><span class="text">Panel Principal</span></a>
        <a href="gestion_Incidentes_Soporte.php" class="menu-item"><i class="fa-solid fa-list"></i><span class="text">Gestión de Incidentes</span></a>
        <a href="repo_sol_Soporte.php" class="menu-item active"><i class="fa-solid fa-book"></i><span class="text">Repositorio de Soluciones</span></a>
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

            <h2 class="fw-bold mb-1">&nbsp&nbspRepositorio de Soluciones</h2>
            <p class="text-muted mb-4">&nbsp&nbsp&nbsp Base de conocimientos y soluciones comunes</p>

            <!-- Buscador -->
            <div class="input-group mb-3" style="max-width: 900px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input id="searchSol" type="text" class="form-control border-start-0" placeholder="Buscar soluciones...">
            </div>

            <!-- Filtros dinámicos -->
            <div class="d-flex gap-2 mb-4">

                <!-- Botón 'Todas' -->
                <button class="btn btn-outline-secondary btn-sm filter-btn" data-filter="all">
                    Todas
                </button>

                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <button class="btn btn-outline-secondary btn-sm filter-btn" 
                            data-filter="<?= $cat['categoria_id']; ?>">
                        <?= htmlspecialchars($cat['nombre']); ?>
                    </button>
                <?php endwhile; ?>

            </div>

            <!-- Tarjeta de solución -->
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>

                    <div class="card shadow-sm border-0 mb-3" style="max-width: 900px;" data-category="<?= $row['categoria_id']; ?>">
                        <div class="card-body">
                            <span class="badge bg-warning text-dark float-end">
                                <?= htmlspecialchars($row['categoria_nombre']); ?>
                            </span>
                            <!-- TÍTULO -->
                            <h5 class="card-title mb-1">
                                <?= htmlspecialchars($row['titulo']); ?>
                            </h5>

                            <!-- DESCRIPCIÓN -->
                            <p class="text-muted mb-3">
                                <?= nl2br(htmlspecialchars($row['detalle_solucion'])); ?>
                            </p>

                            <!-- PASOS (si tienes pasos dentro del detalle, puedes formatearlos luego) -->

                            <hr>

                            <!-- TAGS (palabras clave separadas por comas) -->
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <?php
                                    $tags = explode(",", $row['palabras_clave']);
                                    foreach ($tags as $tag):
                                        $tag = trim($tag);
                                        if ($tag !== ""):
                                ?>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($tag); ?></span>
                                <?php endif; endforeach; ?>
                            </div>

                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No hay soluciones registradas aún.</p>
            <?php endif; ?>
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
        const searchInput = document.getElementById("searchSol");
        const cards = document.querySelectorAll(".sol-card");

        searchInput.addEventListener("input", () => {
            const query = searchInput.value.toLowerCase().trim();

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();

                // Mostrar u ocultar
                if (text.includes(query)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    </script>
   <script>
        document.addEventListener("DOMContentLoaded", () => {
            const botones = document.querySelectorAll(".filter-btn");
            const tarjetas = document.querySelectorAll(".card[data-category]");

            // Si no hay botones o tarjetas, salir sin errores
            if (!botones.length || !tarjetas.length) return;

            botones.forEach(boton => {
                boton.addEventListener("click", () => {
                    const filtro = boton.dataset.filter; // ej: "all" o "3" (categoria_id)

                    tarjetas.forEach(card => {
                        const categoria = card.dataset.category; // string

                        // Mostrar si filtro es 'all' o coincide con data-category
                        if (filtro === "all" || categoria === filtro) {
                            card.style.display = "block";
                        } else {
                            card.style.display = "none";
                        }
                    });

                    // Marcar botón activo (opcional — ajusta clase CSS .active si la tienes)
                    botones.forEach(b => b.classList.remove("active"));
                    boton.classList.add("active");
                });
            });
        });
    </script>

</body>
</html>
