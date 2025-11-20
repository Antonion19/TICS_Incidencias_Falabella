<?php
include 'conexion.php';

$id = intval($_GET['id'] ?? 0);

$sql = "SELECT 
            descripcion_sol,
            tipo_solucion,
            tiempo_solucion,
            es_solucion_final,
            created_at
        FROM respuesta
        WHERE incidencia_id = ?
        AND cod_est_obj = 1
        ORDER BY respuesta_id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<h5 class='text-center text-muted'>Esta incidencia está en proceso</h5>";
    exit;
}

$row = $result->fetch_assoc();

echo "
    <p><strong>Tipo de solución:</strong> " . htmlspecialchars($row['tipo_solucion']) . "</p>
    <p><strong>Tiempo empleado:</strong> " . htmlspecialchars($row['tiempo_solucion']) . "</p>
    <p><strong>Solución final:</strong> " . ($row['es_solucion_final'] ? 'Sí' : 'No') . "</p>
    <hr>
    <p><strong>Descripción:</strong></p>
    <p>" . nl2br(htmlspecialchars($row['descripcion_sol'])) . "</p>
";
?>
