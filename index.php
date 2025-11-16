<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - Sistema de Tickets TI</title>
<link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-box">
    <img src="img/logo.png" alt="logo">
    <h2>Sistema de Tickets TI</h2>

    <form action="inicio_rol.php" method="POST">
        <input type="text" name="username" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Iniciar Sesión</button>
    </form>
</div>

</body>
</html>
