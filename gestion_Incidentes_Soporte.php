<?php include 'sesion.php'; ?>
<!-- index.html -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Sistema de Tickets - Layout</title>

  <!-- Bootstrap (solo CSS, no JS necesario para este layout) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

  <!-- Font Awesome (iconos) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

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
        <a href="gestion_Incidentes_Soporte.php" class="menu-item active"><i class="fa-solid fa-list"></i><span class="text">Gestión de Incidentes</span></a>
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
    <main class="main">
        <div class="container-fluid px-4 py-4">

            <h2 class="fw-bold mb-1">&nbsp;&nbsp;Gestión de Incidentes</h2>
            <p class="text-muted mb-4">&nbsp;&nbsp;&nbsp;  Administra y resuelve los tickets del sistema</p>

            <!-- FILTROS -->
            <div class="d-flex gap-2 mb-3">

                <button class="btn btn-outline-secondary btn-sm filtro-btn" data-filter="asignados">
                    Asignados a mí
                </button>

                <button class="btn btn-outline-secondary btn-sm filtro-btn" data-filter="abiertos">
                    Abiertos
                </button>

                <button class="btn btn-outline-secondary btn-sm filtro-btn" data-filter="progreso">
                    En Progreso
                </button>

                <button class="btn btn-outline-secondary btn-sm filtro-btn" data-filter="resueltos">
                    Resueltos
                </button>

            </div>

            <!-- TABLA DE GESTIÓN DE INCIDENTES -->
            <div class="table-responsive">
                <table id="tablaGestionInc" class="table table-striped table-bordered align-middle" style="width:100%">
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
                            <th>F. Creación</th>
                            <th>F. Cierre</th>
                            <th>Repositorio</th> <!-- NUEVA COLUMNA -->
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>

                            <td>1</td>

                            <td>Error en sistema POS</td>

                            <td>El POS no procesa pagos</td>

                            <td>Software</td>

                            <td><span class="badge bg-danger">Crítica</span></td>

                            <td><span class="badge bg-primary">En Progreso</span></td>

                            <td>Juan Pérez</td>

                            <td>Ana García</td>

                            <td>2025-11-13</td>

                            <td>—</td>

                            <td>No</td>

                            <td>

                                <!-- BOTÓN SI NO TIENE NADIE ASIGNADO -->
                                <button class="btn btn-sm btn-success asignarme-btn">
                                    <i class="fa-solid fa-hand"></i> Asignarme
                                </button>

                                <!-- BOTÓN VER SOLUCIÓN (SI NO ESTÁ ASIGNADO A MÍ) -->
                                <button class="btn btn-sm btn-outline-dark ver-sol-btn">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- BOTÓN REGISTRAR SOLUCIÓN (SI YO LO TENGO ASIGNADO) -->
                                <button class="btn btn-sm btn-primary registrar-sol-btn">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <!-- BOTÓN LIBERAR INCIDENCIA -->
                                <button class="btn btn-sm btn-warning liberar-btn">
                                    <i class="fa-solid fa-unlock"></i>
                                </button>

                                <!-- BOTÓN AGREGAR AL REPOSITORIO (SOLO SI CERRADO) -->
                                <button class="btn btn-sm btn-dark agregar-repo-btn">
                                    <i class="fa-solid fa-book"></i>
                                </button>

                            </td>

                        </tr>
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
        // ===============================
        //   CONFIGURACIÓN INICIAL
        // ===============================

        // Este valor lo reemplazaremos con PHP más adelante
        const usuarioActualID = window.USUARIO_ACTUAL_ID || null;

        // Inicializar DataTable
        let tabla = null;

        $(document).ready(function () {

            tabla = $("#tablaGestionInc").DataTable({
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                },
                columnDefs: [
                    { targets: [0], width: "40px" },
                    { targets: [10], width: "80px" },   // Columna Repositorio
                    { targets: [11], width: "160px" }   // Columna Acciones
                ]
            });

            // ===============================
            //   FILTROS SUPERIORES
            // ===============================

            $(".filtro-btn").on("click", function () {

                const filtro = $(this).data("filter");

                tabla.columns().search(""); // limpiar filtros previos

                switch (filtro) {

                    case "asignados":
                        // El backend deberá colocar ID en la columna "Asignado A"
                        tabla.column(7).search(usuarioActualID);
                        break;

                    case "abiertos":
                        tabla.column(5).search("Abierto");
                        break;

                    case "progreso":
                        tabla.column(5).search("En proceso");
                        break;

                    case "resueltos":
                        tabla.column(5).search("Cerrado");
                        break;

                    default:
                        tabla.search("");
                }

                tabla.draw();
            });

        });


        // ===============================
        //   GENERADOR DE BOTONES
        // ===============================

        // rowData = datos completos de la fila
        function generarAcciones(rowData) {

            const asignadoA = rowData[7]; 
            const estado = rowData[5];
            const repo = rowData[10];

            let botones = "";

            // Caso 1: No asignado → Mostrar botón "Asignarme"
            if (!asignadoA || asignadoA.trim() === "" || asignadoA === "No asignado") {
                botones += `
                    <button class="btn btn-sm btn-outline-primary btn-asignar" data-id="${rowData[0]}">
                        <i class="fa-solid fa-user-check"></i> Asignarme
                    </button>`;
                return botones;
            }

            // Caso 2: Está asignado a otro → solo ver solución
            if (asignadoA != usuarioActualID) {
                botones += `
                    <button class="btn btn-sm btn-outline-dark btn-ver" data-id="${rowData[0]}">
                        <i class="fa-solid fa-eye"></i> Ver
                    </button>`;
                return botones;
            }

            // Caso 3: Está asignado a mí → acciones completas
            if (asignadoA == usuarioActualID) {

                // --- Registrar solución (solo si no está cerrado)
                if (estado !== "Cerrado") {
                    botones += `
                        <button class="btn btn-sm btn-success btn-solucion" data-id="${rowData[0]}">
                            <i class="fa-solid fa-pen"></i> Registrar Solución
                        </button>`;
                }

                // --- Liberar incidencia (solo si no está cerrado)
                if (estado !== "Cerrado") {
                    botones += `
                        <button class="btn btn-sm btn-warning btn-liberar" data-id="${rowData[0]}">
                            <i class="fa-solid fa-share"></i> Liberar
                        </button>`;
                }

                // --- Agregar al repositorio (solo si está cerrado y NO está ya agregado)
                if (estado === "Cerrado" && repo === "No") {
                    botones += `
                        <button class="btn btn-sm btn-info btn-repo" data-id="${rowData[0]}">
                            <i class="fa-solid fa-book"></i> Repo
                        </button>`;
                }

                // --- Ver solución siempre disponible
                botones += `
                    <button class="btn btn-sm btn-outline-dark btn-ver" data-id="${rowData[0]}">
                        <i class="fa-solid fa-eye"></i>
                    </button>`;
            }

            return botones;
        }


        // ===============================
        //   EVENTOS DE BOTONES
        // ===============================

        // Aún no implementamos lógica PHP, solo estructuras de captura

        $(document).on("click", ".btn-asignar", function () {
            const id = $(this).data("id");
            console.log("Asignar incidente:", id);
        });

        $(document).on("click", ".btn-solucion", function () {
            const id = $(this).data("id");
            console.log("Registrar solución:", id);
        });

        $(document).on("click", ".btn-liberar", function () {
            const id = $(this).data("id");
            console.log("Liberar incidente:", id);
        });

        $(document).on("click", ".btn-repo", function () {
            const id = $(this).data("id");
            console.log("Agregar a repositorio:", id);
        });

        $(document).on("click", ".btn-ver", function () {
            const id = $(this).data("id");
            console.log("Ver solución:", id);
        });

    </script>
</body>
</html>
