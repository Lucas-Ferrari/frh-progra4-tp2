<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("cambiar_password.php");
}

$passwordActual = isset($_POST["password_actual"]) ? (string) $_POST["password_actual"] : "";
$passwordNueva = isset($_POST["password_nueva"]) ? (string) $_POST["password_nueva"] : "";
$passwordNuevaRepetir = isset($_POST["password_nueva_repetir"]) ? (string) $_POST["password_nueva_repetir"] : "";

// El id_usuario sale de la SESIÓN, nunca del formulario: así nadie
// puede cambiar la contraseña de otro mandando un id distinto.
$idUsuario = (int) $_SESSION["id_usuario"];

if ($passwordActual === "" || $passwordNueva === "" || $passwordNuevaRepetir === "") {
    redirigir("cambiar_password.php?error=campos");
}
if (strlen($passwordNueva) < PASSWORD_LONGITUD_MINIMA) {
    redirigir("cambiar_password.php?error=password_corta");
}
if ($passwordNueva !== $passwordNuevaRepetir) {
    redirigir("cambiar_password.php?error=password_distinta");
}

// Volvemos a pedir la contraseña actual aunque ya esté logueado.
$usuario = consultarUno($conexion, "SELECT password FROM usuarios WHERE id_usuario = ?", "i", [$idUsuario]);

if ($usuario === null || !password_verify($passwordActual, $usuario["password"])) {
    redirigir("cambiar_password.php?error=password_actual");
}
if ($passwordNueva === $passwordActual) {
    redirigir("cambiar_password.php?error=password_igual");
}

$hash = password_hash($passwordNueva, PASSWORD_DEFAULT);

ejecutar(
    $conexion,
    "UPDATE usuarios SET password = ?, debe_cambiar_password = 0 WHERE id_usuario = ?",
    "si",
    [$hash, $idUsuario]
);

$_SESSION["debe_cambiar_password"] = 0;

redirigir(panelSegunRol(rolActual()) . "?ok=password");
