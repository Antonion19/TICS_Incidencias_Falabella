<?php
require_once "conexion.php";
require "vendor/autoload.php";     // Asegúrate de esta ruta
use Dompdf\Dompdf;  

// ───────────────────────────────
// Validar parámetro
// ───────────────────────────────
if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

// ───────────────────────────────
// Obtener información del incidente
// ───────────────────────────────
$sql = "
SELECT 
    i.incidencia_id,
    i.titulo,
    i.descripcion,
    c.nombre AS categoria_nombre,
    i.prioridad,
    i.estado,

    CONCAT(e1.nombre, ' ', e1.apellido) AS creador_nombre,
    CONCAT(e2.nombre, ' ', e2.apellido) AS asignado_nombre,

    i.created_at,
    i.fecha_cierre,

    CASE 
        WHEN r.repositorio_id IS NULL THEN 'No'
        ELSE 'Sí'
    END AS en_repositorio
FROM incidencia i
LEFT JOIN categoria_incidencia c ON i.categoria_id = c.categoria_id
LEFT JOIN empleado e1 ON i.reportado_por_emp = e1.empleado_id
LEFT JOIN empleado e2 ON i.asignado_a_emp = e2.empleado_id
LEFT JOIN repositorio_soluciones r ON r.incidencia_id = i.incidencia_id
WHERE i.incidencia_id = $id
LIMIT 1
";



// Obtener solución (si existe)
$sqlSol = "
    SELECT descripcion_sol, tipo_solucion, tiempo_solucion, es_solucion_final
    FROM respuesta
    WHERE incidencia_id = $id
    ORDER BY respuesta_id DESC
    LIMIT 1
";
$resSol = $conn->query($sqlSol);
$sol = $resSol->fetch_assoc();




$res = $conn->query($sql);
$inc = $res->fetch_assoc();

if (!$inc) {
    die("Incidente no encontrado.");
}

// ───────────────────────────────
// ¿PDF o vista normal? 
// ───────────────────────────────
if (isset($_GET['pdf'])) {

    $dompdf = new Dompdf();

    // ───────────────────────────────
    // Construir HTML del PDF
    // ───────────────────────────────
    $html = "
    <html>
    <head>
        <style>

            /* Fuente general */
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 12px;
                color: #333;
                margin: 40px;
            }

            /* Encabezado */
            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .logo {
                width: 110px;
                height: auto;
                margin-bottom: 10px;
            }

            .title {
                font-size: 20px;
                font-weight: bold;
                color: #444;
                margin-top: 5px;
            }

            .sub-title {
                font-size: 13px;
                color: #777;
                margin-top: 2px;
            }

            /* Contenedor del detalle */
            .info-box {
                margin-top: 25px;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 15px;
            }

            /* Título de sección */
            .section-title {
                background: #78be20;
                color: white;
                padding: 6px 10px;
                font-size: 13px;
                font-weight: bold;
                margin-bottom: 10px;
                border-radius: 4px;
            }

            /* Tabla */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 5px;
            }

            td {
                padding: 9px;
                vertical-align: top;
                border-bottom: 1px solid #ececec;
            }

            .label {
                width: 28%;
                font-weight: bold;
                color: #555;
                background: #f7f7f7;
            }

            /* Solución */
            .solucion-box {
                margin-top: 20px;
                padding: 12px;
                background: #f9fff5;
                border-left: 4px solid #78be20;
            }

            .no-sol {
                margin-top: 10px;
                padding: 12px;
                background: #fff3f3;
                border-left: 4px solid #cc0000;
                color: #a30000;
                font-weight: bold;
            }

            /* Pie del documento */
            .footer {
                text-align: center;
                margin-top: 25px;
                font-size: 11px;
                color: #777;
            }

        </style>
    </head>
    <body>

        <!-- ENCABEZADO -->
        <div class='header'>
            <div class='title'>Detalle del Incidente #{$inc['incidencia_id']}</div>
            <div class='sub-title'>Sistema de Gestión de Incidentes – Saga Falabella</div>
        </div>

        <!-- CAJA DE INFORMACIÓN -->
        <div class='info-box'>

            <div class='section-title'>Información General</div>

            <table>
                <tr><td class='label'>Título</td><td>{$inc['titulo']}</td></tr>
                <tr><td class='label'>Descripción</td><td>{$inc['descripcion']}</td></tr>
                <tr><td class='label'>Categoría</td><td>{$inc['categoria_nombre']}</td></tr>
                <tr><td class='label'>Prioridad</td><td>{$inc['prioridad']}</td></tr>
                <tr><td class='label'>Estado</td><td>{$inc['estado']}</td></tr>
            </table>

            <div class='section-title'>Responsables</div>

            <table>
                <tr><td class='label'>Creado por</td><td>{$inc['creador_nombre']}</td></tr>
                <tr><td class='label'>Asignado a</td><td>".($inc['asignado_nombre'] ?: "No asignado")."</td></tr>
            </table>

            <div class='section-title'>Fechas</div>

            <table>
                <tr><td class='label'>Fecha de creación</td><td>{$inc['created_at']}</td></tr>
                <tr><td class='label'>Fecha de cierre</td><td>".($inc['fecha_cierre'] ?: "—")."</td></tr>
                <tr><td class='label'>Registrado en repositorio</td><td>{$inc['en_repositorio']}</td></tr>
            </table>

            <div class='section-title'>Solución Registrada</div>";

            // === Mostrar solución o mensaje de no disponible ===
            if ($sol) {
                $html .= "
                <div class='solucion-box'>
                    <strong>Tipo:</strong> {$sol['tipo_solucion']}<br><br>
                    <strong>Descripción:</strong><br>{$sol['descripcion_sol']}<br><br>
                    <strong>Tiempo estimado/final:</strong> {$sol['tiempo_solucion']}<br><br>
                    <strong>Solución final:</strong> ".($sol['es_solucion_final'] ? "Sí" : "No")."
                </div>";
            } else {
                $html .= "
                <div class='no-sol'>
                    No existe una solución registrada para este incidente.
                </div>";
            }

    $html .= "
        </div>

        <div class='footer'>
            Reporte generado automáticamente por el Sistema de Tickets – ".date('d/m/Y H:i')."
        </div>

    </body>
    </html>
    ";

    // Cargar HTML
    $dompdf->loadHtml($html);

    // Tamaño Estándar A4
    $dompdf->setPaper("A4", "portrait");

    $dompdf->render();
    $dompdf->stream("Incidente_$id.pdf", ["Attachment" => false]);
    exit;
}

// Si no es PDF, muestra vista de prueba
echo "<h3>Vista previa</h3>";
echo "<pre>";
print_r($inc);
echo "</pre>";
?>
