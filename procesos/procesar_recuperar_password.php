<?php

require_once(__DIR__ . "/../includes/funciones.php");
require_once(__DIR__ . "/../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("recuperar_password.php");
}

const RECUPERACION_MINUTOS = 15;
const RECUPERACION_MAX_INTENTOS = 5;

$paso = campo($_POST, "paso");
$rec = isset($_SESSION["recuperacion"]) ? $_SESSION["recuperacion"] : null;

// ---------- Paso 1: pedir el código ----------
if ($paso === "email") {
    $email = strtolower(campo($_POST, "email"));
    if (!emailValido($email)) {
        volverConError("recuperar_password.php", "email");
    }

    $usuario = consultarUno($conexion, "SELECT id_usuario FROM usuarios WHERE email = ? AND activo = 1", "s", [$email]);
    $codigo = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);

    // Si el email no existe se sigue igual (sin mandar nada) para no revelar qué emails están registrados.
    $mailEnviado = false;
    if ($usuario !== null) {
        $cuerpo = "Tu código para recuperar la contraseña es: " . $codigo . "\n\n" .
            "Vence en " . RECUPERACION_MINUTOS . " minutos. Si no lo pediste, ignorá este mensaje.\n";
        $headers = "From: no-responder@banco.local\r\n" .
            "MIME-Version: 1.0\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n";
        $asunto = "=?UTF-8?B?" . base64_encode("Código de recuperación de contraseña") . "?=";
        // El @ evita el warning si el servidor no tiene configurado un servidor de correo.
        $mailEnviado = @mail($email, $asunto, $cuerpo, $headers);
    }

    $_SESSION["recuperacion"] = [
        "id_usuario" => $usuario !== null ? (int) $usuario["id_usuario"] : 0,
        "email" => $email,
        "hash" => password_hash($codigo, PASSWORD_DEFAULT),
        "expira" => time() + RECUPERACION_MINUTOS * 60,
        "intentos" => 0,
        "verificado" => false,
    ];
    // Debug: se muestra una sola vez en la página (consola, y en pantalla si el mail falló).
    $_SESSION["flash_codigo"] = ["codigo" => $codigo, "mail_enviado" => $mailEnviado];

    redirigir("recuperar_password.php");
}

if ($rec === null || time() > $rec["expira"]) {
    unset($_SESSION["recuperacion"]);
    redirigir("recuperar_password.php?error=codigo_vencido");
}

// ---------- Paso 2: validar el código ----------
if ($paso === "codigo") {
    $_SESSION["recuperacion"]["intentos"]++;

    if ($_SESSION["recuperacion"]["intentos"] > RECUPERACION_MAX_INTENTOS) {
        unset($_SESSION["recuperacion"]);
        redirigir("recuperar_password.php?error=codigo_intentos");
    }
    if ($rec["id_usuario"] === 0 || !password_verify(campo($_POST, "codigo"), $rec["hash"])) {
        redirigir("recuperar_password.php?error=codigo");
    }

    $_SESSION["recuperacion"]["verificado"] = true;
    redirigir("recuperar_password.php");
}

// ---------- Paso 3: nueva contraseña (solo con el código ya validado) ----------
if ($paso === "password" && $rec["verificado"]) {
    $password = isset($_POST["password"]) ? (string) $_POST["password"] : "";
    $passwordRepetir = isset($_POST["password_repetir"]) ? (string) $_POST["password_repetir"] : "";

    if (strlen($password) < PASSWORD_LONGITUD_MINIMA) {
        volverConError("recuperar_password.php", "password_corta");
    }
    if ($password !== $passwordRepetir) {
        volverConError("recuperar_password.php", "password_distinta");
    }

    ejecutar(
        $conexion,
        "UPDATE usuarios SET password = ?, debe_cambiar_password = 0 WHERE id_usuario = ?",
        "si",
        [password_hash($password, PASSWORD_DEFAULT), $rec["id_usuario"]]
    );
    unset($_SESSION["recuperacion"]);
    redirigir("login.php?ok=password_recuperada");
}

redirigir("recuperar_password.php");
