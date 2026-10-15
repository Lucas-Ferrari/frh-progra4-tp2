<?php

// ABM de cuentas (solo administradores).
// accion = alta | modificar | baja | reactivar

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("admin/cuentas.php");
}

$accion = campo($_POST, "accion");
$idCuenta = (int) campo($_POST, "id_cuenta");

// ---------------------------------------------------------------------
if ($accion === "alta") {
    $idUsuario = (int) campo($_POST, "id_usuario");
    $tipo = campo($_POST, "tipo");
    $moneda = campo($_POST, "moneda");
    $textoSaldo = campo($_POST, "saldo_inicial");
    $saldoInicial = 0.0;

    if ($textoSaldo !== "" && (float) str_replace(",", ".", $textoSaldo) != 0) {
        $saldoInicial = leerMonto($textoSaldo);
        if ($saldoInicial === null) {
            volverConError("admin/cuenta_form.php", "monto");
        }
    }

    if (!in_array($tipo, TIPOS_CUENTA, true) || !in_array($moneda, MONEDAS, true)) {
        volverConError("admin/cuenta_form.php", "campos");
    }

    $cliente = consultarUno(
        $conexion,
        "SELECT id_usuario FROM usuarios WHERE id_usuario = ? AND id_rol = ? AND activo = 1",
        "ii",
        [$idUsuario, ROL_CLIENTE]
    );
    if ($cliente === null) {
        volverConError("admin/cuenta_form.php", "campos");
    }

    mysqli_begin_transaction($conexion);
    try {
        crearCuenta($conexion, $idUsuario, $tipo, $moneda, $saldoInicial);
        mysqli_commit($conexion);
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        volverConError("admin/cuenta_form.php", "db");
    }

    redirigir("admin/cuentas.php?ok=alta");
}

// Para el resto de las acciones la cuenta tiene que existir.
$cuenta = consultarUno(
    $conexion,
    "SELECT c.*, (SELECT COUNT(*) FROM movimientos m WHERE m.id_cuenta = c.id_cuenta) AS cantidad_movimientos
     FROM cuentas c WHERE c.id_cuenta = ?",
    "i",
    [$idCuenta]
);

if ($cuenta === null) {
    redirigir("admin/cuentas.php?error=no_encontrado");
}

// ---------------------------------------------------------------------
if ($accion === "modificar") {
    $tipo = campo($_POST, "tipo");
    // Si la cuenta tiene movimientos el select de moneda viene deshabilitado
    // (no se envía) y la moneda no se puede cambiar.
    $moneda = $cuenta["cantidad_movimientos"] > 0 ? $cuenta["moneda"] : campo($_POST, "moneda");

    if (!in_array($tipo, TIPOS_CUENTA, true) || !in_array($moneda, MONEDAS, true)) {
        volverConError("admin/cuenta_form.php?id=" . $idCuenta, "campos");
    }

    ejecutar($conexion, "UPDATE cuentas SET tipo = ?, moneda = ? WHERE id_cuenta = ?", "ssi", [$tipo, $moneda, $idCuenta]);
    redirigir("admin/cuentas.php?ok=modificacion");
}

// ---------------------------------------------------------------------
if ($accion === "baja") {
    if ((float) $cuenta["saldo"] != 0) {
        redirigir("admin/cuentas.php?error=cuenta_saldo");
    }

    // Una cuenta con movimientos no se borra (perderíamos el historial):
    // se da de baja lógica. Si no tiene movimientos, se borra físicamente.
    if ($cuenta["cantidad_movimientos"] > 0) {
        ejecutar($conexion, "UPDATE cuentas SET activa = 0 WHERE id_cuenta = ?", "i", [$idCuenta]);
        redirigir("admin/cuentas.php?ok=baja_logica");
    }

    ejecutar($conexion, "DELETE FROM cuentas WHERE id_cuenta = ?", "i", [$idCuenta]);
    redirigir("admin/cuentas.php?ok=baja");
}

// ---------------------------------------------------------------------
if ($accion === "reactivar") {
    ejecutar($conexion, "UPDATE cuentas SET activa = 1 WHERE id_cuenta = ?", "i", [$idCuenta]);
    redirigir("admin/cuentas.php?estado=todas&ok=reactivado");
}

redirigir("admin/cuentas.php");
