<?php

// Acciones sobre las reservas del cliente (cliente/reservas.php).
// accion = crear | ingresar | retirar | cerrar
// La lógica (transacciones, saldos, rendimientos) está en includes/inversiones.php.

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");
require_once(__DIR__ . "/../includes/inversiones.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("cliente/reservas.php");
}

$accion = campo($_POST, "accion");
$volver = "cliente/reservas.php";

// ---------------------------------------------------------------------
if ($accion === "crear") {
    $idCuenta = (int) campo($_POST, "id_cuenta");
    $nombre = campo($_POST, "nombre");

    // Meta y monto inicial son opcionales: vacío = sin meta / sin plata todavía.
    $textoMeta = campo($_POST, "meta");
    $meta = $textoMeta === "" ? null : leerMonto($textoMeta);
    $textoMonto = campo($_POST, "monto");
    $montoInicial = $textoMonto === "" ? 0.0 : leerMonto($textoMonto);

    if ($meta === null && $textoMeta !== "") {
        volverConError($volver, "reserva_meta");
    }
    if ($montoInicial === null) {
        volverConError($volver, "monto");
    }

    $error = crearReserva($conexion, $_SESSION["id_usuario"], $idCuenta, $nombre, $meta, $montoInicial);
    if ($error !== null) {
        volverConError($volver, $error);
    }
    redirigir($volver . "?ok=reserva_creada");
}

// ---------------------------------------------------------------------
if (in_array($accion, ["ingresar", "retirar", "cerrar"], true)) {
    $idReserva = (int) campo($_POST, "id_reserva");
    $monto = 0.0;

    if ($accion !== "cerrar") {
        $monto = leerMonto(campo($_POST, "monto"));
        if ($monto === null) {
            volverConError($volver, "monto");
        }
    }

    $error = moverReserva($conexion, $_SESSION["id_usuario"], $idReserva, $accion, $monto);
    if ($error !== null) {
        volverConError($volver, $error);
    }

    $mensajes = ["ingresar" => "reserva_ingreso", "retirar" => "reserva_retiro", "cerrar" => "reserva_cerrada"];
    redirigir($volver . "?ok=" . $mensajes[$accion]);
}

redirigir($volver);
