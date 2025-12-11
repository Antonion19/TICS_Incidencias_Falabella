<?php
include 'sesion.php';
include 'conexion.php';

// AGREGAR ESTO AL PRINCIPIO DEL ARCHIVO, después de los includes
error_reporting(0); // Desactivar visualización de errores
ini_set('display_errors', 0);

// Prevenir reenvío de formulario POST al recargar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$incidencias = [];

$sql = "
    SELECT 
        i.incidencia_id,
        i.titulo,
        i.descripcion,
        c.nombre AS categoria_nombre,
        i.prioridad,
        i.estado,

        -- Creador de la incidencia
        CONCAT(e1.nombre, ' ', e1.apellido) AS creador_nombre,

        -- Asignado A (mostrar nombre pero con ID oculto)
        e2.empleado_id AS asignado_id,
        CONCAT(e2.nombre, ' ', e2.apellido) AS asignado_nombre,

        i.created_at,
        i.fecha_cierre,

        -- Saber si está en el repositorio
        CASE 
            WHEN r.repositorio_id IS NULL THEN 'No'
            ELSE 'Sí'
        END AS en_repositorio

    FROM incidencia i
    LEFT JOIN categoria_incidencia c 
        ON i.categoria_id = c.categoria_id
    LEFT JOIN empleado e1 
        ON i.reportado_por_emp = e1.empleado_id
    LEFT JOIN empleado e2 
        ON i.asignado_a_emp = e2.empleado_id
    LEFT JOIN repositorio_soluciones r
        ON r.incidencia_id = i.incidencia_id

    ORDER BY i.incidencia_id DESC
";

$result = $conn->query($sql);

while ($fila = $result->fetch_assoc()) {
    $incidencias[] = $fila;
}

/* ============================================================
   ACCIÓN: ASIGNAR INCIDENTE AL USUARIO LOGUEADO
   ============================================================ */
if (isset($_GET['asignar_id'])) {

    // Aseguramos que la sesión tiene el ID del empleado
    if (!isset($_SESSION['empleado_id'])) {
        die("ERROR: No existe empleado_id en la sesión");
    }

    $miID = intval($_SESSION['empleado_id']);  // ← ESTE ES EL CORRECTO
    $incidenciaID = intval($_GET['asignar_id']);

    // Actualizar la incidencia
    $sqlAsignar = "UPDATE incidencia 
                   SET asignado_a_emp = $miID 
                   WHERE incidencia_id = $incidenciaID";

    if (!$conn->query($sqlAsignar)) {
        die("ERROR SQL: " . $conn->error);
    }

    // Recargar la página
    header("Location: gestion_Incidentes_Soporte.php");
    exit();
}

/* ============================================================
   ACCIÓN: LIBERAR INCIDENTE (QUITAR ASIGNACIÓN)
   ============================================================ */
if (isset($_GET['liberar_id'])) {

    $incidenciaID = intval($_GET['liberar_id']);

    // Actualizar la incidencia - establecer asignado_a_emp como NULL
    $sqlLiberar = "UPDATE incidencia 
                   SET asignado_a_emp = NULL 
                   WHERE incidencia_id = $incidenciaID";

    if (!$conn->query($sqlLiberar)) {
        die("ERROR SQL: " . $conn->error);
    }

    // Recargar la página
    header("Location: gestion_Incidentes_Soporte.php");
    exit();
}

/* ============================================================
   ACCIÓN: REGISTRAR SOLUCIÓN (vía AJAX)
   ============================================================ */
if ($_POST['action'] ?? '' === 'registrar_solucion') {
    
    $incidenciaID = intval($_POST['incidencia_id']);
    $descripcionSol = $conn->real_escape_string($_POST['descripcion_sol']);
    $tipoSolucion = $conn->real_escape_string($_POST['tipo_solucion'] ?? '');
    $tiempoSolucion = $conn->real_escape_string($_POST['tiempo_solucion'] ?? '');
    $esSolucionFinal = $_POST['es_solucion_final'] === '1';
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Insertar en la tabla respuesta
        $sqlRespuesta = "INSERT INTO respuesta 
                        (incidencia_id, descripcion_sol, tipo_solucion, tiempo_solucion, es_solucion_final) 
                        VALUES 
                        ($incidenciaID, '$descripcionSol', '$tipoSolucion', '$tiempoSolucion', " . ($esSolucionFinal ? '1' : '0') . ")";
        
        if (!$conn->query($sqlRespuesta)) {
            throw new Exception("Error al insertar respuesta: " . $conn->error);
        }
        
        $respuestaID = $conn->insert_id;
        
        // 2. Actualizar el estado de la incidencia
        if ($esSolucionFinal) {
            // Si es solución final, cerrar el incidente
            $sqlIncidencia = "UPDATE incidencia 
                             SET estado = 'Cerrado', fecha_cierre = NOW() 
                             WHERE incidencia_id = $incidenciaID";
        } else {
            // Si no es solución final, cambiar a "En Proceso"
            $sqlIncidencia = "UPDATE incidencia 
                             SET estado = 'En Proceso' 
                             WHERE incidencia_id = $incidenciaID";
        }
        
        if (!$conn->query($sqlIncidencia)) {
            throw new Exception("Error al actualizar incidencia: " . $conn->error);
        }
        
        $conn->commit();
        
        // Respuesta JSON
        echo json_encode(['success' => true, 'message' => 'Solución guardada correctamente']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit();
}


/* ============================================================
   ACCIÓN: GUARDAR EN REPOSITORIO (vía AJAX)
   ============================================================ */
if (isset($_POST['action']) && $_POST['action'] === 'guardar_repositorio') {
    
    // Limpiar cualquier output anterior
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: application/json');
    
    try {
        // Validar que todos los campos requeridos estén presentes
        $required_fields = ['incidencia_id', 'titulo', 'tipo_solucion', 'palabras_clave'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                throw new Exception("El campo '$field' es requerido");
            }
        }

        $incidenciaID = intval($_POST['incidencia_id']);
        $titulo = $conn->real_escape_string(trim($_POST['titulo']));
        $tipoSolucion = $conn->real_escape_string(trim($_POST['tipo_solucion']));
        $palabrasClave = $conn->real_escape_string(trim($_POST['palabras_clave']));
        $detalleSolucion = isset($_POST['detalle_solucion']) ? $conn->real_escape_string(trim($_POST['detalle_solucion'])) : '';

        // Iniciar transacción
        $conn->begin_transaction();

        // 1. Obtener el respuesta_id
        $sqlRespuesta = "SELECT respuesta_id FROM respuesta WHERE incidencia_id = $incidenciaID ORDER BY respuesta_id DESC LIMIT 1";
        $resultRespuesta = $conn->query($sqlRespuesta);
        
        if (!$resultRespuesta) {
            throw new Exception("Error en consulta de respuesta: " . $conn->error);
        }
        
        if ($resultRespuesta->num_rows === 0) {
            throw new Exception("No se encontró una solución registrada para esta incidencia. Debe registrar una solución primero.");
        }
        
        $respuestaData = $resultRespuesta->fetch_assoc();
        $respuestaID = $respuestaData['respuesta_id'];

        // 2. Insertar en repositorio_soluciones
        $sqlRepositorio = "INSERT INTO repositorio_soluciones 
                          (incidencia_id, respuesta_id, titulo, detalle_solucion, tipo_solucion, palabras_clave) 
                          VALUES 
                          ($incidenciaID, $respuestaID, '$titulo', '$detalleSolucion', '$tipoSolucion', '$palabrasClave')";
        
        if (!$conn->query($sqlRepositorio)) {
            throw new Exception("Error al insertar en repositorio: " . $conn->error);
        }

        $conn->commit();

        echo json_encode([
            'success' => true, 
            'message' => 'Solución guardada en el repositorio correctamente'
        ]);

    } catch (Exception $e) {
        // CORRECCIÓN: Esta línea estaba mal
        if ($conn) {
            $conn->rollback();
        }
        
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
    exit();
}
?>

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


    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

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
                    En Proceso
                </button>

                <button class="btn btn-outline-secondary btn-sm filtro-btn" data-filter="resueltos">
                    Cerrados
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
                            <th>Detalle</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidencias as $row): ?>
                            <tr>

                                <td><?= $row['incidencia_id'] ?></td>

                                <td><?= htmlspecialchars($row['titulo']) ?></td>

                                <td><?= htmlspecialchars($row['descripcion']) ?></td>

                                <td><?= htmlspecialchars($row['categoria_nombre']) ?></td>

                                <!-- PRIORIDAD -->
                                <td>
                                    <?php
                                        $p = strtolower($row['prioridad']);
                                        $class = "bg-secondary";
                                        if ($p == "alta") $class = "bg-danger";
                                        if ($p == "media") $class = "bg-warning";
                                        if ($p == "baja") $class = "bg-success";
                                        if ($p == "urgente") $class = "bg-dark";
                                    ?>
                                    <span class="badge <?= $class ?>">
                                        <?= ucfirst($row['prioridad']) ?>
                                    </span>
                                </td>

                                <!-- ESTADO -->
                                <td>
                                    <?php
                                        $e = strtolower($row['estado']);
                                        $classE = "bg-secondary";
                                        if ($e == "abierto") $classE = "bg-success";
                                        if ($e == "en proceso") $classE = "bg-primary";
                                        if ($e == "cerrado") $classE = "bg-dark";
                                    ?>
                                    <span class="badge <?= $classE ?>">
                                        <?= ucfirst($row['estado']) ?>
                                    </span>
                                </td>

                                <td><?= htmlspecialchars($row['creador_nombre']) ?></td>

                                <!-- ASIGNADO A -->
                                <td data-id="<?= $row['asignado_id'] ?>">
                                    <?= $row['asignado_nombre'] ?: "No asignado" ?>
                                </td>

                                <td><?= $row['created_at'] ?></td>

                                <td><?= $row['fecha_cierre'] ?: "—" ?></td>

                                <td><?= $row['en_repositorio'] ?></td>
                                <td class="text-center">
                                    <a href="detalle_incidente.php?id=<?= $row['incidencia_id'] ?>&pdf=1" 
                                    class="btn btn-sm btn-danger btn-detalle-pdf" target="_blank">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                </td>
                                <!-- ACCIONES (vacío, lo llena JS) -->
                                <td class="acciones"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
  </div>
    <!-- Modal para Registrar Solución -->
    <div class="modal fade" id="modalSolucion" tabindex="-1" aria-labelledby="modalSolucionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSolucionLabel">Registrar Solución</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formSolucion">
                        <input type="hidden" id="incidencia_id_solucion" name="incidencia_id">
                        
                        <div class="mb-3">
                            <label for="descripcion_sol" class="form-label">Descripción de la Solución *</label>
                            <textarea class="form-control" id="descripcion_sol" name="descripcion_sol" 
                                    rows="5" placeholder="Describe detalladamente la solución aplicada..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_solucion" class="form-label">Tipo de Solución</label>
                                    <input type="text" class="form-control" id="tipo_solucion" name="tipo_solucion" 
                                        placeholder="Ej: Reinicio, Actualización, Configuración..." required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tiempo_solucion" class="form-label">Tiempo Empleado</label>
                                    <input type="text" class="form-control" id="tiempo_solucion" name="tiempo_solucion" 
                                        placeholder="Ej: 30 minutos, 2 horas, 1 día..." required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarSolucion">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar (En Proceso)
                    </button>
                    <button type="button" class="btn btn-primary" id="btnCerrarIncidente">
                        <i class="fa-solid fa-check-circle"></i> Cerrar Incidente
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Subir al Repositorio -->
    <div class="modal fade" id="modalRepositorio" tabindex="-1" aria-labelledby="modalRepositorioLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRepositorioLabel">Subir Solución al Repositorio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formRepositorio">
                        <input type="hidden" id="incidencia_id_repo" name="incidencia_id">
                        
                        <div class="mb-3">
                            <label for="titulo_repo" class="form-label">Título de la Solución *</label>
                            <input type="text" class="form-control" id="titulo_repo" name="titulo_repo" 
                                placeholder="Ej: Solución para error de conexión de red" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo_solucion_repo" class="form-label">Tipo de Solución *</label>
                            <input type="text" class="form-control" id="tipo_solucion_repo" name="tipo_solucion_repo" 
                                placeholder="Ej: Configuración de red, Reinicio de servicio..." required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="palabras_clave" class="form-label">Palabras Clave *</label>
                            <input type="text" class="form-control" id="palabras_clave" name="palabras_clave" 
                                placeholder="Ej: red, conexión, firewall, configuración (separadas por comas)" required>
                            <div class="form-text">Separa cada palabra clave con comas</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="detalle_solucion" class="form-label">Detalle de la Solución</label>
                            <textarea class="form-control" id="detalle_solucion" name="detalle_solucion" 
                                    rows="4" placeholder="Detalles adicionales sobre la solución..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarRepositorio">
                        <i class="fa-solid fa-book"></i> Guardar en Repositorio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Variables globales
    let modalSolucion = null;
    let modalRepositorio = null;
    let incidenciaActual = null;

    $(document).ready(function () {
        // Inicializar ambos modales
        modalSolucion = new bootstrap.Modal(document.getElementById('modalSolucion'));
        modalRepositorio = new bootstrap.Modal(document.getElementById('modalRepositorio'));
        
        // ... el resto de tu código DataTables ...
    });

    /* =========================================================
    MODAL REGISTRAR SOLUCIÓN
    ========================================================= */

    $(document).on("click", ".btn-solucion", function () {
        incidenciaActual = $(this).data("id");
        $("#incidencia_id_solucion").val(incidenciaActual);
        
        // Limpiar formulario
        $("#formSolucion")[0].reset();
        
        // Mostrar modal
        modalSolucion.show();
    });

    // Botón Guardar Solución (sin cerrar incidente)
    $("#btnGuardarSolucion").on("click", function () {
        guardarSolucion(false); // false = no es solución final
    });

    // Botón Cerrar Incidente
    $("#btnCerrarIncidente").on("click", function () {
        guardarSolucion(true); // true = es solución final
    });

    function guardarSolucion(esSolucionFinal) {
        const formData = new FormData();
        formData.append('incidencia_id', incidenciaActual);
        formData.append('descripcion_sol', $("#descripcion_sol").val());
        formData.append('tipo_solucion', $("#tipo_solucion").val());
        formData.append('tiempo_solucion', $("#tiempo_solucion").val());
        formData.append('es_solucion_final', esSolucionFinal ? '1' : '0');
        formData.append('action', 'registrar_solucion');

        // Validación de campos obligatorios
        if (!$("#descripcion_sol").val().trim()) {
            alert("Por favor, ingresa la descripción de la solución");
            return;
        }
        
        if (!$("#tipo_solucion").val().trim()) {
            alert("Por favor, ingresa el tipo de solución");
            return;
        }
        
        if (!$("#tiempo_solucion").val().trim()) {
            alert("Por favor, ingresa el tiempo empleado");
            return;
        }

        // Mostrar loading en el botón
        const btn = esSolucionFinal ? $("#btnCerrarIncidente") : $("#btnGuardarSolucion");
        const originalText = btn.html();
        btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Procesando...');
        btn.prop('disabled', true);

        // Enviar por AJAX
        fetch('gestion_Incidentes_Soporte.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal y recargar página
                modalSolucion.hide();
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            alert("Error al guardar la solución");
            console.error('Error:', error);
        })
        .finally(() => {
            // Restaurar botón
            btn.html(originalText);
            btn.prop('disabled', false);
        });
    }

    /* =========================================================
    MODAL SUBIR AL REPOSITORIO
    ========================================================= */

    $(document).on("click", ".btn-repo", function () {
        incidenciaActual = $(this).data("id");
        $("#incidencia_id_repo").val(incidenciaActual);
        
        // Limpiar formulario
        $("#formRepositorio")[0].reset();
        
        // Mostrar modal
        modalRepositorio.show();
    });

    // Botón Guardar en Repositorio
    $("#btnGuardarRepositorio").on("click", function () {
        guardarEnRepositorio();
    });

    function guardarEnRepositorio() {
        const formData = new FormData();
        formData.append('incidencia_id', incidenciaActual);
        formData.append('titulo', $("#titulo_repo").val());
        formData.append('tipo_solucion', $("#tipo_solucion_repo").val());
        formData.append('palabras_clave', $("#palabras_clave").val());
        formData.append('detalle_solucion', $("#detalle_solucion").val());
        formData.append('action', 'guardar_repositorio');

        // Validación de campos obligatorios
        if (!$("#titulo_repo").val().trim()) {
            alert("Por favor, ingresa el título de la solución");
            return;
        }
        
        if (!$("#tipo_solucion_repo").val().trim()) {
            alert("Por favor, ingresa el tipo de solución");
            return;
        }
        
        if (!$("#palabras_clave").val().trim()) {
            alert("Por favor, ingresa las palabras clave");
            return;
        }

        // Mostrar loading
        const btn = $("#btnGuardarRepositorio");
        const originalText = btn.html();
        btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Guardando...');
        btn.prop('disabled', true);

        fetch('gestion_Incidentes_Soporte.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            console.log("=== RESPUESTA COMPLETA DEL SERVIDOR ===");
            console.log(text);
            console.log("=== FIN RESPUESTA ===");
            
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    modalRepositorio.hide();
                    
                    // Recargar limpiamente
                    setTimeout(() => {
                        window.location.href = window.location.href.split('?')[0];
                    }, 500);
                } else {
                    alert("Error: " + data.message);
                }
            } catch (e) {
                console.error("NO SE PUDO PARSEAR COMO JSON. La respuesta es:", text);
                alert("Error del servidor. La respuesta no es JSON válido. Ver consola para detalles.");
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
            alert("Error de conexión: " + error.message);
        })
        .finally(() => {
            btn.html(originalText);
            btn.prop('disabled', false);
        });
    }

    // ... el resto de tu código (DataTables, generarAcciones, etc.)
</script>
  <!-- SCRIPT TOGGLE SIDEBAR -->
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
    <script>
        window.USUARIO_ACTUAL_ID = <?= intval($empleado_id) ?>;
    </script>
    <!-- SCRIPT PRINCIPAL -->
    <script>
    /* =========================================================
    CONFIGURACIÓN INICIAL
    ========================================================= */

    const usuarioActualID = window.USUARIO_ACTUAL_ID || null;
    let tabla = null;

    $(document).ready(function () {

        tabla = $("#tablaGestionInc").DataTable({
            pageLength: 10,
            
            lengthMenu: [5, 10, 25, 50],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: 'Exportar Excel', className: 'btn-export-excel' },
                { extend: 'pdfHtml5', text: 'Exportar PDF',  orientation: 'landscape',exportOptions: { columns: ':visible' }, className: 'btn-export-pdf' },
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            columnDefs: [
                { targets: [0], width: "40px" },
                { targets: [10], width: "80px" },
                { targets: [11], width: "200px" }
            ],
            drawCallback: function () {
                aplicarBotones();
            }
        });

        /* =========================================================
        FILTRO PERSONALIZADO: ASIGNADOS A MÍ (por nombre)
        ========================================================= */

        const nombreSesion = "<?= $nombre_completo ?>"; // nombre del usuario logueado

        $.fn.dataTable.ext.search.push(function (settings, data) {

            // Si no está activo el filtro, no filtramos nada
            if (window.FILTRO_ACTUAL !== "asignados") return true;

            // Columna 7 = Asignado A
            const asignadoTexto = data[7] || "";

            // Si contiene el nombre del usuario → mostrar fila
            return asignadoTexto.includes(nombreSesion);
        });

        /* =========================================================
        FILTROS SUPERIORES
        ========================================================= */
        $(".filtro-btn").on("click", function () {

                window.FILTRO_ACTUAL = $(this).data("filter") || null;

                // Limpia filtros de texto
                tabla.columns().search("");

                switch (window.FILTRO_ACTUAL) {

                    case "abiertos":
                        tabla.column(5).search("Abierto");
                        break;

                    case "progreso":
                        tabla.column(5).search("En Progreso");
                        break;

                    case "resueltos":
                        tabla.column(5).search("Cerrado");
                        break;

                    case "asignados":
                        // SOLO activa el filtro personalizado
                        // (el custom filter lo hace todo)
                        break;

                    default:
                        tabla.search("");
                }

                tabla.draw();
            });
    }); // END READY


    /* =========================================================
    FUNCIÓN PARA RENDERIZAR BOTONES AUTOMÁTICAMENTE
    ========================================================= */
    function aplicarBotones() {
        tabla.rows().every(function (rowIdx) {
            const data = this.data();
            const $tdAcciones = $(this.node()).find("td:last");
            $tdAcciones.html(generarAcciones(data, rowIdx));
        });
    }



    /* =========================================================
    GENERADOR DE BOTONES - VERSIÓN CORREGIDA
    ========================================================= */
    function generarAcciones(data, rowIndex) {
        const nodo = tabla.row(rowIndex).node();
        const tdAsignado = nodo.querySelector("td[data-id]");
        const asignadoID = tdAsignado ? parseInt(tdAsignado.getAttribute("data-id")) : null;
        
        // EXTRAER TEXTO LIMPIO del HTML del estado
        const estadoHTML = data[5];
        const estado = extraerTextoDeEstado(estadoHTML).toLowerCase();
        
        const repo = data[10].trim().toLowerCase();
        const incidenciaID = data[0];

        console.log("Debug - Fila:", {
            incidenciaID: incidenciaID,
            estado: estado, // ← Ahora será "cerrado" no el HTML
            repo: repo,
            asignadoID: asignadoID,
            usuarioActualID: usuarioActualID,
            textoAsignado: data[7]
        });

        // ----------------------------------------------
        //  CASO 0: SIN ASIGNADO → Mostrar SOLO "Asignarme"
        // ----------------------------------------------
        if (!asignadoID || isNaN(asignadoID) || data[7].includes("No asignado")) {
            return `
                <button class="btn btn-sm btn-outline-primary btn-asignar" data-id="${incidenciaID}">
                    <i class="fa-solid fa-user-check"></i> Asignarme
                </button>`;
        }

        // ----------------------------------------------
        //  CASO 1: ASIGNADO A OTRO → Mostrar SOLO VER
        // ----------------------------------------------
        if (asignadoID != usuarioActualID) {
            return `
                <button class="btn btn-sm btn-outline-dark btn-ver" data-id="${incidenciaID}">
                    <i class="fa-solid fa-eye"></i> Ver
                </button>`;
        }

        // ----------------------------------------------
        //  CASO 2: ASIGNADO A MÍ → Aplicar reglas
        // ----------------------------------------------

        // PRIMERO verificar CERRADO + NO REPO (para que tenga prioridad)
        if (estado.includes("cerrado") && (repo === "no" || repo === "")) {
            return `
                <button class="btn btn-sm btn-info btn-repo" data-id="${incidenciaID}">
                    <i class="fa-solid fa-book"></i> Subir al Repo
                </button>`;
        }

        // SEGUNDO verificar CERRADO + SÍ REPO
        if (estado.includes("cerrado") && (repo === "sí" || repo === "si")) {
            return `<span class="text-muted">Incidente cerrado</span>`;
        }

        // TERCERO verificar ABIERTO o EN PROCESO
        if (estado.includes("abierto") || estado.includes("proceso")) {
            return `
                <button class="btn btn-sm btn-success btn-solucion" data-id="${incidenciaID}">
                    <i class="fa-solid fa-pen"></i> Registrar solución
                </button>
                <button class="btn btn-sm btn-warning btn-liberar" data-id="${incidenciaID}">
                    <i class="fa-solid fa-share"></i> Liberar
                </button>`;
        }

        // Fallback (no debería pasar)
        return `<span class="text-muted">Sin acciones</span>`;
    }

    // FUNCIÓN PARA EXTRAER TEXTO DEL HTML DEL ESTADO
    function extraerTextoDeEstado(html) {
        // Si ya es texto limpio, devolver tal cual
        if (!html.includes('<')) return html.trim();
        
        // Si tiene HTML, extraer el texto usando una expresión regular simple
        const text = html.replace(/<[^>]*>/g, '').trim();
        return text || html;
    }

    /* =========================================================
    EVENTOS DE BOTONES
    ========================================================= */

    $(document).on("click", ".btn-asignar", function () {
        const id = $(this).data("id");
        window.location.href = "gestion_Incidentes_Soporte.php?asignar_id=" + id;
    });

    $(document).on("click", ".btn-solucion", function () {
        console.log("Registrar solución:", $(this).data("id"));
    });

    $(document).on("click", ".btn-liberar", function () {
        const id = $(this).data("id");
        if (confirm("¿Estás seguro de que quieres liberar este incidente? Ya no estarás asignado a él.")) {
            window.location.href = "gestion_Incidentes_Soporte.php?liberar_id=" + id;
        }
    });

    $(document).on("click", ".btn-repo", function () {
        incidenciaActual = $(this).data("id");
        
        // Verificación rápida - asegurarnos que tiene solución
        if (!confirm("¿Estás seguro de subir esta solución al repositorio? Asegúrate de que la solución esté completa.")) {
            return;
        }
        
        $("#incidencia_id_repo").val(incidenciaActual);
        
        // Limpiar formulario
        $("#formRepositorio")[0].reset();
        
        // Mostrar modal
        modalRepositorio.show();
    });

    $(document).on("click", ".btn-ver", function () {
        console.log("Ver solución:", $(this).data("id"));
    });
    </script>

</body>
</html>
