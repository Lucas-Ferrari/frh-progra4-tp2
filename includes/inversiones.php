<?php

// Plazos fijos y reservas: tasas, cálculos y movimientos de plata.
// Las pantallas (cliente/) y los procesos (procesos/) solo llaman a estas
// funciones: así la regla de negocio vive en un solo lugar.

require_once(__DIR__ . "/funciones.php");

// ---------------------------------------------------------------------
// Tasas y condiciones (TNA = tasa nominal anual, en %).
// Están acá y no en la base para que el TP sea simple de leer; si el banco
// necesitara cambiarlas seguido, pasarían a una tabla de configuración.
// ---------------------------------------------------------------------
const TNA_PLAZO_FIJO = ["ARS" => 32.00, "USD" => 2.00];
const TNA_RESERVA = ["ARS" => 28.00, "USD" => 1.50];
const PLAZO_FIJO_DIAS = [30, 60, 90, 180];
const PLAZO_FIJO_MINIMO = ["ARS" => 1000, "USD" => 100];
const RESERVA_NOMBRE_MAXIMO = 40;

// ---------------------------------------------------------------------
// Movimiento en la cuenta: actualiza el saldo y deja el registro.
// Va siempre DENTRO de una transacción abierta por quien la llama
// (si algo falla después, el rollback deshace las dos cosas).
// $monto con signo: negativo = sale de la cuenta, positivo = entra.
// ---------------------------------------------------------------------
function moverSaldoCuenta($conexion, $idCuenta, $tipo, $monto, $descripcion)
{
    ejecutar($conexion, "UPDATE cuentas SET saldo = saldo + ? WHERE id_cuenta = ?", "di", [$monto, $idCuenta]);
    ejecutar(
        $conexion,
        "INSERT INTO movimientos (id_cuenta, tipo, monto, descripcion) VALUES (?, ?, ?, ?)",
        "isds",
        [$idCuenta, $tipo, $monto, mb_substr($descripcion, 0, 150)]
    );
}

// Cuenta activa del usuario, bloqueada hasta el commit (FOR UPDATE) para que
// dos operaciones simultáneas no lean el mismo saldo. null si no es suya.
function cuentaPropiaBloqueada($conexion, $idCuenta, $idUsuario)
{
    return consultarUno(
        $conexion,
        "SELECT * FROM cuentas WHERE id_cuenta = ? AND id_usuario = ? AND activa = 1 FOR UPDATE",
        "ii",
        [$idCuenta, $idUsuario]
    );
}

// =====================================================================
// PLAZOS FIJOS
// =====================================================================

// Constituye un plazo fijo: saca el capital de la cuenta y guarda el
// interés pactado. Devuelve null si salió bien o el código de error.
function constituirPlazoFijo($conexion, $idUsuario, $idCuenta, $capital, $dias)
{
    if (!in_array($dias, PLAZO_FIJO_DIAS, true)) {
        return "plazo";
    }

    mysqli_begin_transaction($conexion);
    try {
        $cuenta = cuentaPropiaBloqueada($conexion, $idCuenta, $idUsuario);

        $error = null;
        if ($cuenta === null) {
            $error = "cuenta";
        } elseif ($capital < PLAZO_FIJO_MINIMO[$cuenta["moneda"]]) {
            $error = "plazo_minimo";
        } elseif ((float) $cuenta["saldo"] < $capital) {
            $error = "saldo";
        }
        if ($error !== null) {
            mysqli_rollback($conexion);
            return $error;
        }

        $tna = TNA_PLAZO_FIJO[$cuenta["moneda"]];

        // El interés se calcula en MySQL con DECIMAL (no con float de PHP)
        // para que los centavos den exactos: capital × TNA × días / 36500.
        ejecutar(
            $conexion,
            "INSERT INTO plazos_fijos (id_cuenta, capital, tna, dias, interes, fecha_vencimiento)
             VALUES (?, ?, ?, ?,
                     ROUND(CAST(? AS DECIMAL(12,2)) * CAST(? AS DECIMAL(5,2)) * ? / 36500, 2),
                     NOW() + INTERVAL ? DAY)",
            "iddisdii",
            [$idCuenta, $capital, $tna, $dias, (string) $capital, $tna, $dias, $dias]
        );

        moverSaldoCuenta($conexion, $idCuenta, "Plazo fijo", -$capital, "Constitución de plazo fijo a " . $dias . " días");

        mysqli_commit($conexion);
        return null;
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        return "db";
    }
}

// Acredita capital + interés de los plazos fijos vencidos.
// No hay una tarea programada (cron) que corra todos los días: se llama al
// abrir las pantallas que muestran saldos (evaluación perezosa). El
// resultado es el mismo que si se hubiera acreditado justo al vencer.
// $idUsuario = null procesa los de todos los clientes (pantallas de empleados).
function acreditarPlazosVencidos($conexion, $idUsuario = null)
{
    $sql = "SELECT p.id_plazo_fijo, p.id_cuenta, p.capital, p.interes, p.dias
            FROM plazos_fijos p
            INNER JOIN cuentas c ON c.id_cuenta = p.id_cuenta
            WHERE p.estado = 'Activo' AND p.fecha_vencimiento <= NOW()";
    $tipos = "";
    $parametros = [];
    if ($idUsuario !== null) {
        $sql .= " AND c.id_usuario = ?";
        $tipos = "i";
        $parametros = [$idUsuario];
    }

    foreach (consultar($conexion, $sql, $tipos, $parametros) as $plazo) {
        // Una transacción por plazo: si uno falla, los otros igual se acreditan.
        mysqli_begin_transaction($conexion);
        try {
            // El "AND estado = 'Activo'" evita acreditar dos veces si dos
            // pedidos llegan juntos: el segundo no modifica ninguna fila.
            $marcado = ejecutar(
                $conexion,
                "UPDATE plazos_fijos SET estado = 'Acreditado', fecha_acreditacion = NOW()
                 WHERE id_plazo_fijo = ? AND estado = 'Activo'",
                "i",
                [$plazo["id_plazo_fijo"]]
            );

            if ($marcado === 1) {
                $total = round((float) $plazo["capital"] + (float) $plazo["interes"], 2);
                moverSaldoCuenta(
                    $conexion,
                    (int) $plazo["id_cuenta"],
                    "Plazo fijo",
                    $total,
                    "Acreditación de plazo fijo a " . $plazo["dias"] . " días (capital + interés)"
                );
            }

            mysqli_commit($conexion);
        } catch (mysqli_sql_exception $ex) {
            mysqli_rollback($conexion);
        }
    }
}

// Plazos fijos del usuario: primero los activos (el que vence antes arriba),
// después el historial de acreditados.
function plazosFijosDelUsuario($conexion, $idUsuario)
{
    return consultar(
        $conexion,
        "SELECT p.*, c.moneda, c.numero_cuenta,
                GREATEST(DATEDIFF(p.fecha_vencimiento, NOW()), 0) AS dias_restantes
         FROM plazos_fijos p
         INNER JOIN cuentas c ON c.id_cuenta = p.id_cuenta
         WHERE c.id_usuario = ?
         ORDER BY p.estado = 'Activo' DESC,
                  CASE WHEN p.estado = 'Activo' THEN p.fecha_vencimiento END ASC,
                  p.fecha_acreditacion DESC",
        "i",
        [$idUsuario]
    );
}

// =====================================================================
// RESERVAS
// =====================================================================

// Suma a cada reserva del usuario lo que rindió desde el último cálculo.
// Interés compuesto diario, como Mercado Pago: cada día rinde sobre lo que
// ya había ganado. Solo cuenta días completos; las horas que sobran quedan
// para el próximo cálculo (por eso fecha_calculo avanza de a días enteros).
function actualizarRendimientoReservas($conexion, $idUsuario)
{
    $pendientes = consultar(
        $conexion,
        "SELECT r.id_reserva, r.fecha_calculo, c.moneda,
                TIMESTAMPDIFF(DAY, r.fecha_calculo, NOW()) AS dias
         FROM reservas r
         INNER JOIN cuentas c ON c.id_cuenta = r.id_cuenta
         WHERE c.id_usuario = ? AND r.activa = 1 AND r.saldo > 0
           AND TIMESTAMPDIFF(DAY, r.fecha_calculo, NOW()) >= 1",
        "i",
        [$idUsuario]
    );

    foreach ($pendientes as $reserva) {
        $tna = TNA_RESERVA[$reserva["moneda"]];
        $dias = (int) $reserva["dias"];

        // MySQL evalúa los SET de izquierda a derecha: rendimientos y saldo
        // usan el saldo VIEJO (saldo se pisa recién en el segundo SET, pero
        // la fórmula del primero ya se calculó) y la fecha avanza al final.
        // El WHERE con la fecha leída evita sumar dos veces el mismo período.
        ejecutar(
            $conexion,
            "UPDATE reservas
             SET rendimientos = rendimientos + ROUND(saldo * (POW(1 + ? / 36500, ?) - 1), 2),
                 saldo = saldo + ROUND(saldo * (POW(1 + ? / 36500, ?) - 1), 2),
                 fecha_calculo = fecha_calculo + INTERVAL ? DAY
             WHERE id_reserva = ? AND fecha_calculo = ?",
            "didiiis",
            [$tna, $dias, $tna, $dias, $dias, $reserva["id_reserva"], $reserva["fecha_calculo"]]
        );
    }
}

function reservasDelUsuario($conexion, $idUsuario)
{
    return consultar(
        $conexion,
        "SELECT r.*, c.moneda, c.numero_cuenta, c.tipo AS tipo_cuenta
         FROM reservas r
         INNER JOIN cuentas c ON c.id_cuenta = r.id_cuenta
         WHERE c.id_usuario = ? AND r.activa = 1
         ORDER BY r.fecha_creacion",
        "i",
        [$idUsuario]
    );
}

// Reserva activa del usuario, bloqueada junto con su cuenta. null si no es suya.
function reservaPropiaBloqueada($conexion, $idReserva, $idUsuario)
{
    return consultarUno(
        $conexion,
        "SELECT r.*, c.moneda, c.saldo AS saldo_cuenta
         FROM reservas r
         INNER JOIN cuentas c ON c.id_cuenta = r.id_cuenta
         WHERE r.id_reserva = ? AND c.id_usuario = ? AND r.activa = 1 AND c.activa = 1
         FOR UPDATE",
        "ii",
        [$idReserva, $idUsuario]
    );
}

// Crea una reserva y, si viene un monto inicial, lo pasa desde la cuenta.
function crearReserva($conexion, $idUsuario, $idCuenta, $nombre, $meta, $montoInicial)
{
    if ($nombre === "" || mb_strlen($nombre) > RESERVA_NOMBRE_MAXIMO) {
        return "reserva_nombre";
    }

    mysqli_begin_transaction($conexion);
    try {
        $cuenta = cuentaPropiaBloqueada($conexion, $idCuenta, $idUsuario);
        if ($cuenta === null) {
            mysqli_rollback($conexion);
            return "cuenta";
        }
        if ($montoInicial > 0 && (float) $cuenta["saldo"] < $montoInicial) {
            mysqli_rollback($conexion);
            return "saldo";
        }

        ejecutar(
            $conexion,
            "INSERT INTO reservas (id_cuenta, nombre, meta, saldo) VALUES (?, ?, ?, ?)",
            "isdd",
            [$idCuenta, $nombre, $meta, $montoInicial]
        );

        if ($montoInicial > 0) {
            moverSaldoCuenta($conexion, $idCuenta, "Reserva", -$montoInicial, 'Ingreso a reserva "' . $nombre . '"');
        }

        mysqli_commit($conexion);
        return null;
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        return "db";
    }
}

// Mueve plata entre la reserva y su cuenta.
// $operacion: "ingresar" (cuenta → reserva) | "retirar" (reserva → cuenta)
// | "cerrar" (retira todo y da de baja la reserva).
function moverReserva($conexion, $idUsuario, $idReserva, $operacion, $monto)
{
    // Antes de mover plata, se suma lo que rindió hasta hoy: si no, al
    // retirar todo quedaría afuera el rendimiento de los últimos días.
    actualizarRendimientoReservas($conexion, $idUsuario);

    mysqli_begin_transaction($conexion);
    try {
        $reserva = reservaPropiaBloqueada($conexion, $idReserva, $idUsuario);
        if ($reserva === null) {
            mysqli_rollback($conexion);
            return "reserva";
        }

        $saldoReserva = (float) $reserva["saldo"];
        if ($operacion === "cerrar") {
            $monto = $saldoReserva;
        }

        $error = null;
        if ($operacion === "ingresar" && (float) $reserva["saldo_cuenta"] < $monto) {
            $error = "saldo";
        } elseif ($operacion === "retirar" && $saldoReserva < $monto) {
            $error = "reserva_saldo";
        }
        if ($error !== null) {
            mysqli_rollback($conexion);
            return $error;
        }

        // Signo desde el punto de vista de la reserva: ingresar suma, retirar/cerrar resta.
        $montoReserva = $operacion === "ingresar" ? $monto : -$monto;
        $nombre = '"' . $reserva["nombre"] . '"';

        ejecutar($conexion, "UPDATE reservas SET saldo = saldo + ? WHERE id_reserva = ?", "di", [$montoReserva, $idReserva]);

        if ($monto > 0) {
            $descripcion = $operacion === "ingresar" ? "Ingreso a reserva " . $nombre : "Retiro de reserva " . $nombre;
            // En la cuenta el signo es el opuesto: lo que entra a la reserva sale de la cuenta.
            moverSaldoCuenta($conexion, (int) $reserva["id_cuenta"], "Reserva", -$montoReserva, $descripcion);
        }

        if ($operacion === "cerrar") {
            ejecutar($conexion, "UPDATE reservas SET activa = 0 WHERE id_reserva = ?", "i", [$idReserva]);
        }

        mysqli_commit($conexion);
        return null;
    } catch (mysqli_sql_exception $ex) {
        mysqli_rollback($conexion);
        return "db";
    }
}

// ¿La cuenta tiene plata invertida? (no se puede dar de baja hasta liberarla)
function cuentaTieneInversiones($conexion, $idCuenta)
{
    $fila = consultarUno(
        $conexion,
        "SELECT
            (SELECT COUNT(*) FROM plazos_fijos WHERE id_cuenta = ? AND estado = 'Activo')
          + (SELECT COUNT(*) FROM reservas WHERE id_cuenta = ? AND activa = 1 AND saldo > 0) AS cantidad",
        "ii",
        [$idCuenta, $idCuenta]
    );
    return (int) $fila["cantidad"] > 0;
}
