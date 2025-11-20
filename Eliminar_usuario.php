<?php
include 'sesion.php';
include 'conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: GestionUsuario_Admin.php");
    exit;
}

$empleado_id = (int) $_GET['id'];

$sql = "DELETE FROM empleado WHERE empleado_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $empleado_id);
    $stmt->execute();
    $stmt->close();
}

// Siempre regresar a la lista
header("Location: GestionUsuario_Admin.php");
exit;
