<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("cliente/mis_datos.php");
}

$idUsuario = (int) $_SESSION["id_usuario"];
$email = strtolower(campo($_POST, "email"));
$passwordActual = isset($_POST["password_actual"]) ? (string) $_POST["password_actual"] : "";

if ($email === "" || $passwordActual === "") {
    volverConError("cliente/mis_datos.php", "campos");
}
if (!emailValido($email)) {
    volverConError("cliente/mis_datos.php", "email");
}

$usuario = consultarUno($conexion, "SELECT password FROM usuarios WHERE id_usuario = ?", "i", [$idUsuario]);
if ($usuario === null || !password_verify($passwordActual, $usuario["password"])) {
    volverConError("cliente/mis_datos.php", "password_actual");
}

$otro = consultarUno($conexion, "SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario <> ?", "si", [$email, $idUsuario]);
if ($otro !== null) {
    volverConError("cliente/mis_datos.php", "email_duplicado");
}

ejecutar($conexion, "UPDATE usuarios SET email = ? WHERE id_usuario = ?", "si", [$email, $idUsuario]);
$_SESSION["email"] = $email;

redirigir("cliente/mis_datos.php?ok=datos");
