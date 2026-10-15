<?php

require_once(__DIR__ . "/../includes/funciones.php");
require_once(__DIR__ . "/../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("registro.php");
}

$nombre = campo($_POST, "nombre");
$apellido = campo($_POST, "apellido");
$dni = campo($_POST, "dni");
$fechaNacimiento = campo($_POST, "fecha_nacimiento");
$email = strtolower(campo($_POST, "email"));
$password = isset($_POST["password"]) ? (string) $_POST["password"] : "";
$passwordRepetir = isset($_POST["password_repetir"]) ? (string) $_POST["password_repetir"] : "";

// ---------- Validaciones del lado del servidor ----------
if ($nombre === "" || $apellido === "" || $dni === "" || $fechaNacimiento === "" || $email === "" || $password === "") {
    volverConError("registro.php", "campos");
}
if (!dniValido($dni)) {
    volverConError("registro.php", "dni");
}
if (!fechaValida($fechaNacimiento)) {
    volverConError("registro.php", "fecha");
}
if (!emailValido($email)) {
    volverConError("registro.php", "email");
}
if (strlen($password) < PASSWORD_LONGITUD_MINIMA) {
    volverConError("registro.php", "password_corta");
}
if ($password !== $passwordRepetir) {
    volverConError("registro.php", "password_distinta");
}
if (usuarioDuplicado($conexion, $email, $dni)) {
    volverConError("registro.php", "duplicado");
}

// ---------- Alta del cliente + caja de ahorro en $0 ----------
// Las dos inserciones van en una transacción: o se guardan ambas o ninguna.
$hash = password_hash($password, PASSWORD_DEFAULT);
$rol = ROL_CLIENTE;

mysqli_begin_transaction($conexion);

try {
    ejecutar(
        $conexion,
        "INSERT INTO usuarios (nombre, apellido, dni, fecha_nacimiento, email, password, id_rol)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        "ssssssi",
        [$nombre, $apellido, $dni, $fechaNacimiento, $email, $hash, $rol]
    );
    $idUsuario = mysqli_insert_id($conexion);

    crearCuenta($conexion, $idUsuario, "Caja de ahorro", "ARS");

    mysqli_commit($conexion);
} catch (mysqli_sql_exception $ex) {
    mysqli_rollback($conexion);
    volverConError("registro.php", "db");
}

redirigir("login.php?ok=registro");
