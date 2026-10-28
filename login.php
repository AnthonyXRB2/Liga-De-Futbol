<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <script src="tema.js" defer></script>
</head>
<body class="pagina-login">
<main class="contenido-login">

<button class="boton-tema-login" type="button" aria-pressed="false">Modo oscuro</button>
<h2>Iniciar Sesión</h2>

<?php if (isset($_SESSION['error_login'])) { ?>
    <p class="mensaje-error"><?php echo htmlspecialchars($_SESSION['error_login'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php unset($_SESSION['error_login']); ?>
<?php } ?>

<form action="validar_login.php" method="POST">

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
