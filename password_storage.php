<?php

// Genera hashes de contraseña y los codifica para su almacenamiento seguro.
function hash_user_password(string $password): string
{
    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($hash === false) {
        throw new RuntimeException("No se pudo proteger la contraseña.");
    }

    return "hash:" . bin2hex($hash);
}

// Recupera el hash binario y rechaza codificaciones mal formadas.
function decode_stored_password_hash(string $storedPassword): string|false
{
    $encodedHash = substr($storedPassword, 5);

    if ($encodedHash === "" || strlen($encodedHash) % 2 !== 0 || !ctype_xdigit($encodedHash)) {
        return false;
    }

    return hex2bin($encodedHash);
}

// Admite hashes actuales y contraseñas legadas durante la migración.
function verify_user_password(string $password, string $storedPassword): bool
{
    if (str_starts_with($storedPassword, "hash:")) {
        $hash = decode_stored_password_hash($storedPassword);

        return $hash !== false && password_verify($password, $hash);
    }

    return hash_equals($storedPassword, $password);
}

// Identifica claves que todavía necesitan migración o actualización.
function user_password_needs_rehash(string $storedPassword): bool
{
    if (!str_starts_with($storedPassword, "hash:")) {
        return true;
    }

    $hash = decode_stored_password_hash($storedPassword);

    return $hash === false || password_needs_rehash($hash, PASSWORD_DEFAULT);
}
