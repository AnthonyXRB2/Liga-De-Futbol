<?php
require_once __DIR__ . '/../../Includes/rutas.php';
// Recupera el mensaje de acceso para mostrarlo junto al formulario.
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="<?php echo app_url('Assets-Css/login.css'); ?>">
</head>
<body class="pagina-login">
<main class="contenido-login">

<!-- Envía las credenciales al controlador de autenticación. -->
<form action="<?php echo app_url('Procesos/Acceso/validar_login.php'); ?>" method="POST">

<img class="logo-login" src="<?php echo app_url('Assets-Imagenes/logo.png'); ?>" alt="Logo de Omni Tech">
<h2>Iniciar Sesión</h2>

<!-- Muestra una sola vez el error devuelto por la validación. -->
<?php if (isset($_SESSION['error_login'])) { ?>
    <p class="mensaje-error"><?php echo htmlspecialchars($_SESSION['error_login'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php unset($_SESSION['error_login']); ?>
<?php } ?>

<label>Usuario</label><br>
<input type="text" name="usuario">

<br><br>

<label>Contraseña</label><br>
<input type="password" name="password">

<br><br>

<button type="submit">Ingresar</button>

</form>

</main>

</body>
</html>