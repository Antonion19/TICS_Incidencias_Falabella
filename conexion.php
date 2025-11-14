<?php
$host = "db.fr-pari1.bengt.wasmernet.com";   // Host del servicio
$port = 10272;                                // Puerto proporcionado
$user = "6b56ac8e7d0580002e21c4d0f7d7";       // Usuario
$pass = "06916b56-ac8e-7e7f-8000-291d38db31bc"; // Password
$db   = "dbPWUkaUgT7utrYpRj8h25dm";            // Nombre de la BD

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>
