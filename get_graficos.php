<?php
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

// ============== 1. Incidentes por mes ==============
$sqlMes = "
    SELECT 
        MONTH(created_at) AS mes,
        SUM(estado IN ('Abierto', 'En proceso')) AS pendientes,
        SUM(estado = 'Cerrado') AS resueltos
    FROM incidencia
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
";
$resMes = $conn->query($sqlMes);

$meses = [];
$pendientes = [];
$resueltos = [];

$nombreMes = ["", "Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];

while($row = $resMes->fetch_assoc()){
    $meses[] = $nombreMes[$row['mes']];
    $pendientes[] = (int)$row['pendientes'];
    $resueltos[] = (int)$row['resueltos'];
}

// ============== 2. Categorías ==============
$sqlCat = "
    SELECT c.nombre, COUNT(*) AS total
    FROM incidencia i
    INNER JOIN categoria_incidencia c
        ON c.categoria_id = i.categoria_id
    GROUP BY c.categoria_id
";
$resCat = $conn->query($sqlCat);

$labelsCat = [];
$dataCat = [];

while($row = $resCat->fetch_assoc()){
    $labelsCat[] = $row['nombre'];
    $dataCat[] = (int)$row['total'];
}

// ============== 3. Estados ==============
$sqlEstado = "
    SELECT estado, COUNT(*) AS total
    FROM incidencia
    GROUP BY estado
";
$resEstado = $conn->query($sqlEstado);

$labelsEst = [];
$dataEst = [];

while($row = $resEstado->fetch_assoc()){
    $labelsEst[] = $row['estado'];
    $dataEst[] = (int)$row['total'];
}

// ============== 4. Prioridades ==============
$sqlPri = "
    SELECT prioridad, COUNT(*) AS total
    FROM incidencia
    GROUP BY prioridad
";
$resPri = $conn->query($sqlPri);

$labelsPri = [];
$dataPri = [];

while($row = $resPri->fetch_assoc()){
    $labelsPri[] = $row['prioridad'];
    $dataPri[] = (int)$row['total'];
}

// ============== Respuesta Final ==============
echo json_encode([
    "meses" => $meses,
    "pendientes" => $pendientes,
    "resueltos" => $resueltos,

    "categorias_labels" => $labelsCat,
    "categorias_data" => $dataCat,

    "estados_labels" => $labelsEst,
    "estados_data" => $dataEst,

    "prioridades_labels" => $labelsPri,
    "prioridades_data" => $dataPri
]);

?>
