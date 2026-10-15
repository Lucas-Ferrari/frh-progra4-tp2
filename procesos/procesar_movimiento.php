<?php

// ABM de movimientos.
//   alta      → depósito / extracción (administradores y operadores)
//   modificar → solo administradores
//   baja      → solo administradores
// Cada operación actualiza el saldo dentro de una transacción.

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN, ROL_OPERADOR]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("admin/movimientos.php");
}

$accion = campo($_POST, "accion");

if (($accion === "modificar" || $accion === "baja") && rolActual() !== ROL_ADMIN) {
    redirigir(panelSegunRol(rolActual()) . "?error=permisos");
}

// Monto con signo según el tipo: depósito suma, extracción resta.
function montoConSigno($tipo, $monto)
{
    return $tipo === "Extracción" ? -$monto : $monto;
}

// ---------------------------------------------------------------------
if ($accion === "alta") {
    $idCuenta = (int) campo($_POST, "id_cuenta");
    $tipo = campo($_POST, "tipo");
    $monto = leerMonto(campo($_POST, "monto"));
    $descripcion = campo($_POST, "descripcion");
    $volver = "admin/movimientos.php?id_cuenta=" . $idCuenta;

    if (!in_array($tipo, ["Depósito", "Extracción"], true)) {
        volverConError($volver, "campos");
    }
    if ($monto === null) {
        volverConError($volver, "monto");
    }
    if ($descripcion === "") {
        $descripcion = $tipo === "Depósito" ? "Depósito por ventanilla" : "Extracción por ventanilla";
    }

    $importe = montoConSigno($tipo, $monto);

    mysqli_begin_transaction($conexion);
    try {
        $cuenta = consultarUno($conexion, "SELECT saldo FROM cuentas WHERE id_cuenta = ? AND activa = 1 FOR UPDATE", "i", [$idCuenta]);

        if ($cuenta === null) {
            mysqli_rollback($conexion);
            volverConError($volver, "cuenta");
        }
        // Una extracción no puede dejar el saldo en negativo.
        if ((float) $cuenta["saldo"] + $importe < 0) {
            mysqli_rollback($conexion);
            volverConError($volver, "saldo");
        }

        ejecutar(
            $conexion,
            "INSERT INTO movimientos (id_cuenta, tipo, monto, descripcion) VALUES (?, ?, ?, ?)",
            "isds",
            [$idCuenta, $tipo, $importe, $descripcion]
        );
        ejecutar($conexion, "UPDATE cuentas SET saldo = saldo + ? WHERE id_cuenta = ?", "di", [$importe, $idCuenta]);

        mysqli_commit($conexion);
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        volverConError($volver, "db");
    }

    redirigir($volver . "&ok=movimiento");
}

// ---------------------------------------------------------------------
// modificar / baja: se busca el movimiento y su cuenta, bloqueando la cuenta.
$idMovimiento = (int) campo($_POST, "id_movimiento");

mysqli_begin_transaction($conexion);

try {
    $movimiento = consultarUno(
        $conexion,
        "SELECT m.*, c.saldo, c.activa FROM movimientos m
         JOIN cuentas c ON c.id_cuenta = m.id_cuenta
         WHERE m.id_movimiento = ? FOR UPDATE",
        "i",
        [$idMovimiento]
    );

    if ($movimiento === null) {
        mysqli_rollback($conexion);
        redirigir("admin/cuentas.php?error=no_encontrado");
    }

    $idCuenta = (int) $movimiento["id_cuenta"];
    $volver = "admin/movimientos.php?id_cuenta=" . $idCuenta;

    // Las transferencias afectan a dos cuentas: no se tocan.
    if ($movimiento["tipo"] === "Transferencia" || !$movimiento["activa"]) {
        mysqli_rollback($conexion);
        redirigir($volver . "&error=no_editable");
    }

    $saldoActual = (float) $movimiento["saldo"];
    $montoViejo = (float) $movimiento["monto"];

    if ($accion === "modificar") {
        $tipo = campo($_POST, "tipo");
        $monto = leerMonto(campo($_POST, "monto"));
        $descripcion = campo($_POST, "descripcion");
        $volverForm = "admin/movimiento_form.php?id=" . $idMovimiento;

        if (!in_array($tipo, ["Depósito", "Extracción"], true)) {
            mysqli_rollback($conexion);
            volverConError($volverForm, "campos");
        }
        if ($monto === null) {
            mysqli_rollback($conexion);
            volverConError($volverForm, "monto");
        }

        $montoNuevo = montoConSigno($tipo, $monto);
        // Recalcular el saldo: se "deshace" el monto viejo y se aplica el nuevo.
        $diferencia = $montoNuevo - $montoViejo;

        if ($saldoActual + $diferencia < 0) {
            mysqli_rollback($conexion);
            volverConError($volverForm, "saldo");
        }

        ejecutar(
            $conexion,
            "UPDATE movimientos SET tipo = ?, monto = ?, descripcion = ? WHERE id_movimiento = ?",
            "sdsi",
            [$tipo, $montoNuevo, $descripcion, $idMovimiento]
        );
        ejecutar($conexion, "UPDATE cuentas SET saldo = saldo + ? WHERE id_cuenta = ?", "di", [$diferencia, $idCuenta]);

        mysqli_commit($conexion);
        redirigir($volver . "&ok=modificacion");
    }

    if ($accion === "baja") {
        // Borrar un depósito resta; borrar una extracción suma.
        if ($saldoActual - $montoViejo < 0) {
            mysqli_rollback($conexion);
            redirigir($volver . "&error=saldo");
        }

        ejecutar($conexion, "DELETE FROM movimientos WHERE id_movimiento = ?", "i", [$idMovimiento]);
        ejecutar($conexion, "UPDATE cuentas SET saldo = saldo - ? WHERE id_cuenta = ?", "di", [$montoViejo, $idCuenta]);

        mysqli_commit($conexion);
        redirigir($volver . "&ok=baja");
    }

    mysqli_rollback($conexion);
} catch (mysqli_sql_exception $ex) {
    mysqli_rollback($conexion);
    redirigir("admin/cuentas.php?error=db");
}

redirigir("admin/movimientos.php");
