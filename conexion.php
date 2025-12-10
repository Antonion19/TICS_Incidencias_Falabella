<?php
$host = "localhost";     // Servidor local
$user = "root";          // Usuario por defecto en XAMPP/WAMP
$pass = "";              // Contraseña (vacío normalmente)
$db   = "falabella_incidencias";  // <-- CAMBIA ESTO por el nombre real de tu base

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>