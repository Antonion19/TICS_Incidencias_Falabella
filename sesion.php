<?php
session_start();
require_once "conexion.php";

// Si no hay usuario logueado, redirige al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

// Obtener datos completos del usuario + empleado
$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT 
            u.usuario_id,
            u.username,
            e.empleado_id,
            CONCAT(e.nombre, ' ', e.apellido) AS nombre_completo,
            e.correo,
            e.telefono,
            e.rol,
            e.area
        FROM usuario u
        INNER JOIN empleado e ON u.empleado_id = e.empleado_id
        WHERE u.usuario_id = ?
          AND u.cod_est_obj = 1
          AND e.cod_est_obj = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Algo raro pasó (usuario borrado o desactivado)
    session_destroy();
    header("Location: index.php");
    exit();
}

$userData = $result->fetch_assoc();

// Guardar en variables de sesión (disponibles en todas las páginas)
$_SESSION['empleado_id']    = $userData['empleado_id'];
$_SESSION['nombre_completo'] = $userData['nombre_completo']; 
$_SESSION['correo']         = $userData['correo'];
$_SESSION['rol']            = $userData['rol'];

// Variables listas para usar directamente en cada página
$empleado_id     = $userData['empleado_id'];
$nombre_completo = $userData['nombre_completo'];
$correo          = $userData['correo'];
$rol             = $userData['rol'];

?>
