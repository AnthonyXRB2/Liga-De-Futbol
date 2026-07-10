<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>

<h2>Iniciar Sesión</h2>

<form action="validar_login.php" method="POST">

<label>Usuario</label><br>
<input type="text" name="usuario">

<br><br>

<label>Contraseña</label><br>
<input type="password" name="password">

<br><br>

<button type="submit">Ingresar</button>

</form>

</body>
</html>