<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != "admin") {
    echo "<h2>Acceso denegado</h2>";
    exit();
}

include("conexion.php");

$usuario = $_POST["usuario"];
$password = $_POST["password"];
$club_id = $_POST["club_id"];

$existe = mysqli_query($conn, "
SELECT id
FROM usuarios
WHERE usuario='$usuario'
");

if(mysqli_num_rows($existe) > 0){

    echo "<h2>Ese usuario ya existe.</h2>";
    echo "<br>";
    echo "<a href='registrar_usuario.php'>Volver</a>";
    exit();

}

$sql = "
INSERT INTO usuarios
(usuario,password,rol,club_id)
VALUES
('$usuario','$password','club','$club_id')
";

mysqli_query($conn,$sql);

header("Location: registrar_usuario.php?ok=1");
exit();
?>