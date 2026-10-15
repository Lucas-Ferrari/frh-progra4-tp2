<?php

// Alta de cliente hecha por un empleado (operador o administrador).

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN, ROL_OPERADOR]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("operador/alta_cliente.php");
}

$nombre = campo($_POST, "nombre");
$apellido = campo($_POST, "apellido");
$dni = campo($_POST, "dni");
$fechaNacimiento = campo($_POST, "fecha_nacimiento");
$email = strtolower(campo($_POST, "email"));

$error = validarDatosPersonales($nombre, $apellido, $dni, $fechaNacimiento, $email);
if ($error !== null) {
    volverConError("operador/alta_cliente.php", $error);
}
if (usuarioDuplicado($conexion, $email, $dni)) {
    volverConError("operador/alta_cliente.php", "duplicado");
}

// Inserta con debe_cambiar_password = 1 y deja la contraseña provisoria
// en la sesión para mostrarla una sola vez.
$password = crearUsuarioConProvisoria($conexion, $nombre, $apellido, $dni, $fechaNacimiento, $email, ROL_CLIENTE);

if ($password === null) {
    volverConError("operador/alta_cliente.php", "db");
}

redirigir("operador/alta_cliente.php?ok=cliente");
