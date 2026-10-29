<?php

// Restringe la migración de claves a ejecuciones administrativas por consola.
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit("Este script solo se puede ejecutar desde la línea de comandos.");
}

require_once(__DIR__ . "/conexion.php");
require_once(__DIR__ . "/password_storage.php");

// Amplía la columna y selecciona las claves que aún no tienen hash.
if (!$conn->query("ALTER TABLE usuarios MODIFY password VARCHAR(255) NOT NULL")) {
    fwrite(STDERR, "No se pudo ampliar la columna password: " . $conn->error . PHP_EOL);
    exit(1);
}

$consulta = $conn->query("SELECT id, password FROM usuarios WHERE password NOT LIKE 'hash:%'");

if (!$consulta) {
    fwrite(STDERR, "No se pudieron leer las contraseñas: " . $conn->error . PHP_EOL);
    exit(1);
}

$actualizar = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
$migradas = 0;

// Protege las contraseñas pendientes y aborta ante cualquier error SQL.
while ($usuario = $consulta->fetch_assoc()) {
    $passwordHash = hash_user_password($usuario["password"]);
    $id = (int) $usuario["id"];
    $actualizar->bind_param("si", $passwordHash, $id);

    if (!$actualizar->execute()) {
        fwrite(STDERR, "No se pudo actualizar la contraseña del usuario ID {$id}: " . $actualizar->error . PHP_EOL);
        exit(1);
    }

    $migradas++;
}

echo "Migración completada: {$migradas} contraseña(s) protegida(s)." . PHP_EOL;
