<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Consulta con JOIN para obtener el rol del empleado
    $sql = "SELECT u.usuario_id, u.password_hash, e.rol 
            FROM usuario u
            INNER JOIN empleado e ON u.empleado_id = e.empleado_id
            WHERE u.username = ? AND u.cod_est_obj = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Comparación simple (luego puedes reemplazarla por password_verify si usas hashing)
        if ($password === $user['password_hash']) {
            $_SESSION['usuario_id'] = $user['usuario_id'];
            $_SESSION['rol'] = $user['rol'];

            // Redirección por rol
            switch ($user['rol']) {
                case 'Administrador':
                    header("Location: inicio_Admin.php");
                    break;
                case 'Soporte TI':
                    header("Location: inicio_Soporte.php");
                    break;
                case 'Empleado':
                    header("Location: inicio_Empleado.php");
                    break;
                default:
                    echo "<script>alert('Rol no reconocido.'); window.location='index.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no encontrado'); window.location='index.php';</script>";
    }
    $stmt->close();
}
$conn->close();
?>
