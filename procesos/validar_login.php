<?php

require_once(__DIR__ . "/../includes/funciones.php");
require_once(__DIR__ . "/../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("login.php");
}

$email = strtolower(campo($_POST, "email"));
$password = isset($_POST["password"]) ? (string) $_POST["password"] : "";
$tipoAcceso = campo($_POST, "tipo_acceso");

// Cada tipo de acceso apunta a la lista de roles que pueden usarlo.
$rolesPermitidos = [
    "empleado" => [ROL_ADMIN, ROL_OPERADOR],
    "cliente" => [ROL_CLIENTE],
];

// Si el tipo no viene o no es válido NO salteamos la validación de rol:
// directamente rechazamos el ingreso.
if (!isset($rolesPermitidos[$tipoAcceso])) {
    volverConError("login.php", "tipo");
}

if ($email === "" || $password === "") {
    volverConError("login.php", "campos");
}

// Sentencia preparada: el email nunca se mezcla con el SQL,
// así que "' OR '1'='1" se busca literalmente como texto.
$usuario = consultarUno($conexion, "SELECT * FROM usuarios WHERE email = ?", "s", [$email]);

// Un único mensaje genérico tanto si el email no existe como si la
// contraseña es incorrecta: así no revelamos qué emails están registrados.
if ($usuario === null || !password_verify($password, $usuario["password"])) {
    volverConError("login.php", "credenciales");
}

if ((int) $usuario["activo"] !== 1) {
    volverConError("login.php", "inactivo");
}

if (!in_array((int) $usuario["id_rol"], $rolesPermitidos[$tipoAcceso], true)) {
    volverConError("login.php", "permisos");
}

// Login correcto: nuevo id de sesión (evita fijación de sesión).
session_regenerate_id(true);

$_SESSION["id_usuario"] = (int) $usuario["id_usuario"];
$_SESSION["nombre"] = $usuario["nombre"];
$_SESSION["apellido"] = $usuario["apellido"];
$_SESSION["email"] = $usuario["email"];
$_SESSION["id_rol"] = (int) $usuario["id_rol"];
$_SESSION["debe_cambiar_password"] = (int) $usuario["debe_cambiar_password"];
$_SESSION["ultimo_acceso"] = time();

if ($_SESSION["debe_cambiar_password"] === 1) {
    redirigir("cambiar_password.php");
}

redirigir(panelSegunRol($_SESSION["id_rol"]));
