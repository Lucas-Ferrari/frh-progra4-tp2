<?php

// Recibe el formulario de cliente/plazos_fijos.php y constituye el plazo fijo.
// La lógica está en includes/inversiones.php; acá solo se lee y se valida
// lo que llegó del formulario.

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");
require_once(__DIR__ . "/../includes/inversiones.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("cliente/plazos_fijos.php");
}

$idCuenta = (int) campo($_POST, "id_cuenta");
$capital = leerMonto(campo($_POST, "monto"));
$dias = (int) campo($_POST, "dias");

if ($capital === null) {
    volverConError("cliente/plazos_fijos.php", "monto");
}

$error = constituirPlazoFijo($conexion, $_SESSION["id_usuario"], $idCuenta, $capital, $dias);

if ($error !== null) {
    volverConError("cliente/plazos_fijos.php", $error);
}

redirigir("cliente/plazos_fijos.php?ok=plazo_fijo");
