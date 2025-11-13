<?php
$host = "localhost";
$user = "root"; // usuario por defecto de XAMPP
$pass = "";     // por defecto sin contraseña
$db   = "falabella_incidencias";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>
