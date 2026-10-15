<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("cliente/transferir.php");
}

$idOrigen = (int) campo($_POST, "id_cuenta_origen");
$numeroDestino = campo($_POST, "numero_destino");
$monto = leerMonto(campo($_POST, "monto"));
$concepto = campo($_POST, "descripcion");

if ($monto === null) {
    volverConError("cliente/transferir.php", "monto");
}

mysqli_begin_transaction($conexion);

try {
    // FOR UPDATE bloquea las filas hasta el commit, para que dos
    // transferencias simultáneas no lean el mismo saldo.
    // La cuenta de origen tiene que ser del usuario logueado.
    $origen = consultarUno(
        $conexion,
        "SELECT * FROM cuentas WHERE id_cuenta = ? AND id_usuario = ? AND activa = 1 FOR UPDATE",
        "ii",
        [$idOrigen, $_SESSION["id_usuario"]]
    );

    $destino = consultarUno(
        $conexion,
        "SELECT * FROM cuentas WHERE numero_cuenta = ? AND activa = 1 FOR UPDATE",
        "s",
        [$numeroDestino]
    );

    $error = null;
    if ($origen === null) {
        $error = "cuenta";
    } elseif ($destino === null || (int) $destino["id_cuenta"] === (int) $origen["id_cuenta"]) {
        $error = "cuenta_destino";
    } elseif ($origen["moneda"] !== $destino["moneda"]) {
        $error = "moneda";
    } elseif ((float) $origen["saldo"] < $monto) {
        $error = "saldo";
    }

    if ($error !== null) {
        mysqli_rollback($conexion);
        volverConError("cliente/transferir.php", $error);
    }

    $descripcionOrigen = "Transferencia a cuenta " . $destino["numero_cuenta"] . ($concepto !== "" ? " - " . $concepto : "");
    $descripcionDestino = "Transferencia de cuenta " . $origen["numero_cuenta"] . ($concepto !== "" ? " - " . $concepto : "");
    $descripcionOrigen = mb_substr($descripcionOrigen, 0, 150);
    $descripcionDestino = mb_substr($descripcionDestino, 0, 150);
    $montoNegativo = -$monto;

    ejecutar($conexion, "UPDATE cuentas SET saldo = saldo - ? WHERE id_cuenta = ?", "di", [$monto, $origen["id_cuenta"]]);
    ejecutar($conexion, "UPDATE cuentas SET saldo = saldo + ? WHERE id_cuenta = ?", "di", [$monto, $destino["id_cuenta"]]);

    ejecutar(
        $conexion,
        "INSERT INTO movimientos (id_cuenta, tipo, monto, descripcion) VALUES (?, 'Transferencia', ?, ?)",
        "ids",
        [$origen["id_cuenta"], $montoNegativo, $descripcionOrigen]
    );
    ejecutar(
        $conexion,
        "INSERT INTO movimientos (id_cuenta, tipo, monto, descripcion) VALUES (?, 'Transferencia', ?, ?)",
        "ids",
        [$destino["id_cuenta"], $monto, $descripcionDestino]
    );

    mysqli_commit($conexion);
} catch (mysqli_sql_exception $ex) {
    mysqli_rollback($conexion);
    volverConError("cliente/transferir.php", "db");
}

redirigir("cliente/movimientos.php?id_cuenta=" . (int) $origen["id_cuenta"] . "&ok=transferencia");
