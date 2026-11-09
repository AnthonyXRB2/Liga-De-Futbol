<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acceso denegado.');
}

$usuarioId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($usuarioId === false || $usuarioId === null) {
    http_response_code(400);
    exit('Usuario no válido.');
}

require_once('conexion.php');

$consultaUsuario = $conn->prepare(
    'SELECT usuarios.id, usuarios.usuario, usuarios.rol, usuarios.club_id, clubes.activo
     FROM usuarios
     LEFT JOIN clubes ON clubes.id = usuarios.club_id
     WHERE usuarios.id = ?'
);
if (!$consultaUsuario) {
    http_response_code(500);
    exit('No se pudo cargar el usuario.');
}
$consultaUsuario->bind_param('i', $usuarioId);
if (!$consultaUsuario->execute()) {
    $consultaUsuario->close();
    http_response_code(500);
    exit('No se pudo cargar el usuario.');
}
$usuario = $consultaUsuario->get_result()->fetch_assoc();
$consultaUsuario->close();
if (!$usuario) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}
if ($usuario['rol'] === 'admin') {
    http_response_code(403);
    exit('Las cuentas administrador solo permiten cambiar la contraseña.');
}

$clubes = [];
if ($usuario['rol'] === 'club') {
    $resultadoClubes = $conn->prepare(
        'SELECT clubes.id, clubes.nombre, clubes.activo,
                EXISTS(
                    SELECT 1 FROM usuarios AS otro
                    WHERE otro.club_id = clubes.id AND otro.id <> ?
                ) AS ocupado
         FROM clubes
         WHERE clubes.activo = 1 OR clubes.id = ?
         ORDER BY clubes.nombre'
    );
    if (!$resultadoClubes) {
        http_response_code(500);
        exit('No se pudieron cargar los clubes.');
    }
    $resultadoClubes->bind_param('ii', $usuarioId, $usuario['club_id']);
    if (!$resultadoClubes->execute()) {
        $resultadoClubes->close();
        http_response_code(500);
        exit('No se pudieron cargar los clubes.');
    }
    $clubes = $resultadoClubes->get_result()->fetch_all(MYSQLI_ASSOC);
    $resultadoClubes->close();
}

$escape = static fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
$errores = [
    'nombre' => 'Ingresa un nombre de usuario válido.',
    'duplicado' => 'Ese nombre de usuario ya está en uso.',
    'club' => 'Selecciona un club activo que no tenga otro usuario asociado.',
];
$error = $errores[$_GET['error'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
<?php include('menu.php'); ?>

<main class="pagina-contenido">
    <h2>Editar usuario</h2>
    <?php if ($error !== '') { ?>
        <p class="mensaje-error"><?php echo $escape($error); ?></p>
    <?php } ?>

    <form id="form-editar-usuario" action="guardar_edicion_usuario.php" method="POST">
        <input type="hidden" name="id" value="<?php echo (int) $usuario['id']; ?>">

        <label for="usuario">Usuario</label>
        <input id="usuario" type="text" name="usuario" maxlength="43" value="<?php echo $escape($usuario['usuario']); ?>" aria-describedby="usuario-ayuda usuario-espacio-error" required>
        <small id="usuario-ayuda">Hasta 4 palabras, mínimo 4 y máximo 10 letras cada una.</small>
        <span id="usuario-espacio-error" class="ayuda-fecha" role="alert" hidden></span>

        <label>Rol</label>
        <p><?php echo $escape(ucfirst($usuario['rol'])); ?> (no editable)</p>

        <?php if ($usuario['rol'] === 'club') { ?>
            <label for="club_id">Club</label>
            <select id="club_id" name="club_id" required>
                <option value="" disabled <?php echo empty($usuario['club_id']) ? 'selected' : ''; ?>>Selecciona un club activo</option>
                <?php foreach ($clubes as $club) { ?>
                    <?php
                    $esActual = (int) $usuario['club_id'] === (int) $club['id'];
                    $deshabilitado = (int) $club['activo'] !== 1 || ((int) $club['ocupado'] === 1 && !$esActual);
                    ?>
                    <option value="<?php echo (int) $club['id']; ?>"
                        <?php echo $esActual ? 'selected' : ''; ?>
                        <?php echo $deshabilitado ? 'disabled' : ''; ?>>
                        <?php echo $escape($club['nombre']); ?><?php echo $deshabilitado ? ' (no disponible)' : ''; ?>
                    </option>
                <?php } ?>
            </select>
        <?php } ?>

        <div class="acciones-formulario">
            <button type="submit">Guardar cambios</button>
            <a href="usuarios.php">Cancelar</a>
        </div>
    </form>
</main>
<script>
function configurarNombreCompuesto(campo, maxEspacios, avisoEspacio) {
    function validar(mostrarAvisoEspacio = false) {
        const palabras = campo.value.split(" ");
        const patron = new RegExp(
            "^\\p{Lu}[\\p{Ll}\\p{M}]*(?: \\p{Lu}[\\p{Ll}\\p{M}]*){0," + maxEspacios + "}$",
            "u"
        );
        const invalido = !patron.test(campo.value)
            || palabras.length > maxEspacios + 1
            || palabras.some((palabra) => {
                const cantidadLetras = Array.from(palabra.matchAll(/\p{L}/gu)).length;
                return cantidadLetras < 4 || cantidadLetras > 10;
            });
        const mensaje = `Usa hasta ${maxEspacios + 1} palabras, con un mínimo de 4 y un máximo de 10 letras por palabra.`;
        const espacioFinal = campo.value.endsWith(" ");

        campo.setCustomValidity(
            espacioFinal ? "Quita el espacio de más antes de guardar."
                : invalido ? mensaje : ""
        );
        avisoEspacio.textContent = "Quita el espacio de más antes de guardar.";
        avisoEspacio.hidden = !mostrarAvisoEspacio || !espacioFinal;
    }

    campo.addEventListener("input", () => {
        let texto = "";
        let letrasEnPalabra = 0;
        let espacios = 0;
        let ultimoFueEspacio = false;

        for (const caracter of campo.value) {
            if (/\p{L}/u.test(caracter)) {
                if (letrasEnPalabra >= 10) {
                    continue;
                }

                const convertido = letrasEnPalabra === 0
                    ? caracter.toLocaleUpperCase("es-UY")
                    : caracter.toLocaleLowerCase("es-UY");
                if (!/^\p{L}$/u.test(convertido)) {
                    continue;
                }
                texto += convertido;
                letrasEnPalabra += 1;
                ultimoFueEspacio = false;
            } else if (
                /\p{M}/u.test(caracter)
                && letrasEnPalabra > 0
                && !ultimoFueEspacio
            ) {
                texto += caracter;
            } else if (
                caracter === " "
                && letrasEnPalabra > 0
                && espacios < maxEspacios
                && !ultimoFueEspacio
            ) {
                texto += caracter;
                espacios += 1;
                letrasEnPalabra = 0;
                ultimoFueEspacio = true;
            }
        }

        campo.value = texto;
        validar();
    });

    campo.addEventListener("blur", validar);
    return validar;
}

const usuario = document.getElementById("usuario");
const errorEspacio = document.getElementById("usuario-espacio-error");
const validarUsuario = configurarNombreCompuesto(usuario, 3, errorEspacio);
const formularioUsuario = document.getElementById("form-editar-usuario");

formularioUsuario.addEventListener("submit", (evento) => {
    validarUsuario(true);
    if (usuario.value.endsWith(" ") || !formularioUsuario.reportValidity()) {
        evento.preventDefault();
    }
});
</script>
</body>
</html>
