<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");
require_once(__DIR__ . "/../includes/inversiones.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

// Primero se ponen al día las inversiones (plazos vencidos y rendimiento de
// reservas), así los saldos y movimientos de abajo ya los incluyen.
acreditarPlazosVencidos($conexion, $_SESSION["id_usuario"]);
actualizarRendimientoReservas($conexion, $_SESSION["id_usuario"]);

// Solo las cuentas del usuario logueado (el id sale de la sesión).
$cuentas = consultar(
    $conexion,
    "SELECT * FROM cuentas WHERE id_usuario = ? AND activa = 1 ORDER BY fecha_apertura",
    "i",
    [$_SESSION["id_usuario"]]
);

// Últimos 5 movimientos de todas las cuentas del usuario, mezclados por fecha.
$ultimosMovimientos = consultar(
    $conexion,
    "SELECT m.*, c.moneda, c.tipo AS tipo_cuenta, c.numero_cuenta
     FROM movimientos m
     INNER JOIN cuentas c ON c.id_cuenta = m.id_cuenta
     WHERE c.id_usuario = ? AND c.activa = 1
     ORDER BY m.fecha DESC, m.id_movimiento DESC
     LIMIT 5",
    "i",
    [$_SESSION["id_usuario"]]
);

// Resumen de inversiones en pesos para las dos tarjetas de acceso.
$resumenReservas = consultarUno(
    $conexion,
    "SELECT COUNT(*) AS cantidad, COALESCE(SUM(r.saldo), 0) AS total
     FROM reservas r INNER JOIN cuentas c ON c.id_cuenta = r.id_cuenta
     WHERE c.id_usuario = ? AND r.activa = 1 AND c.moneda = 'ARS'",
    "i",
    [$_SESSION["id_usuario"]]
);
$resumenPlazos = consultarUno(
    $conexion,
    "SELECT COUNT(*) AS cantidad, COALESCE(SUM(p.capital + p.interes), 0) AS total, MIN(p.fecha_vencimiento) AS proximo
     FROM plazos_fijos p INNER JOIN cuentas c ON c.id_cuenta = p.id_cuenta
     WHERE c.id_usuario = ? AND p.estado = 'Activo' AND c.moneda = 'ARS'",
    "i",
    [$_SESSION["id_usuario"]]
);

$titulo = "Mi panel";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1>Hola, <?php echo e($_SESSION["nombre"]); ?></h1>
    <div class="acciones">
        <a href="transferir.php" class="boton">Transferir</a>
        <a href="mis_datos.php" class="boton boton-secundario">Mis datos</a>
    </div>
</div>

<div class="panel-cliente">
    <section>
        <h2>Mis cuentas</h2>

        <?php if (count($cuentas) === 0): ?>
            <div class="tarjeta vacio">No tenés cuentas activas. Acercate a la sucursal para abrir una.</div>
        <?php else: ?>
            <div class="cuentas">
                <?php foreach ($cuentas as $cuenta): ?>
                    <a class="cuenta" href="movimientos.php?id_cuenta=<?php echo (int) $cuenta["id_cuenta"]; ?>"
                       aria-label="Ver movimientos de la <?php echo e(strtolower($cuenta["tipo"])); ?> en <?php echo e($cuenta["moneda"]); ?>">
                        <span class="cuenta-moneda" aria-hidden="true"><?php echo $cuenta["moneda"] === "USD" ? "US$" : "$"; ?></span>
                        <span class="cuenta-datos">
                            <span class="cuenta-tipo"><?php echo e(ucfirst($cuenta["tipo"])); ?> · <?php echo e($cuenta["moneda"]); ?></span>
                            <span class="cuenta-numero">N.º <?php echo e($cuenta["numero_cuenta"]); ?></span>
                        </span>
                        <span class="cuenta-saldo">
                            <span class="monto"><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></span>
                            <span class="texto-suave">Saldo disponible</span>
                        </span>
                        <span class="cuenta-ir" aria-hidden="true">→</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section aria-labelledby="titulo-inversiones">
        <h2 id="titulo-inversiones">Ahorro e inversiones</h2>

        <!-- Dos accesos con el dato que más importa de cada uno (montos en pesos) -->
        <div class="accesos-inversion">
            <a class="acceso-inversion" href="reservas.php">
                <span class="acceso-inversion-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7h-1V6a3 3 0 0 0-3-3H5a2 2 0 0 0-2 2v12a3 3 0 0 0 3 3h13a2 2 0 0 0 2-2v-9a2 2 0 0 0-2-2z"/><path d="M16 14h.01"/><path d="M3 7h15"/></svg>
                </span>
                <span class="acceso-inversion-datos">
                    <span class="acceso-inversion-titulo">Reservas</span>
                    <span class="texto-suave">
                        <?php if ((int) $resumenReservas["cantidad"] > 0): ?>
                            <?php echo e(formatoMonto($resumenReservas["total"])); ?> guardados · rinde <?php echo e(number_format(TNA_RESERVA["ARS"], 0)); ?>% TNA
                        <?php else: ?>
                            Separá plata que rinde todos los días (<?php echo e(number_format(TNA_RESERVA["ARS"], 0)); ?>% TNA)
                        <?php endif; ?>
                    </span>
                </span>
                <span class="cuenta-ir" aria-hidden="true">→</span>
            </a>

            <a class="acceso-inversion" href="plazos_fijos.php">
                <span class="acceso-inversion-icono" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M9 2h6"/></svg>
                </span>
                <span class="acceso-inversion-datos">
                    <span class="acceso-inversion-titulo">Plazo fijo</span>
                    <span class="texto-suave">
                        <?php if ((int) $resumenPlazos["cantidad"] > 0): ?>
                            Cobrás <?php echo e(formatoMonto($resumenPlazos["total"])); ?> · próximo vence el <?php echo e(formatoFecha($resumenPlazos["proximo"], false)); ?>
                        <?php else: ?>
                            Tasa fija del <?php echo e(number_format(TNA_PLAZO_FIJO["ARS"], 0)); ?>% TNA a 30, 60, 90 o 180 días
                        <?php endif; ?>
                    </span>
                </span>
                <span class="cuenta-ir" aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    <section>
        <h2>Últimos movimientos</h2>

        <div class="tarjeta ultimos-movimientos">
            <?php if (count($ultimosMovimientos) === 0): ?>
                <p class="vacio">Todavía no tenés movimientos.</p>
            <?php else: ?>
                <ul class="lista-movimientos">
                    <?php foreach ($ultimosMovimientos as $mov): ?>
                        <?php $ingreso = $mov["monto"] >= 0; ?>
                        <li>
                            <span class="movimiento-icono <?php echo $ingreso ? "ingreso" : "egreso"; ?>" aria-hidden="true"><?php echo $ingreso ? "↓" : "↑"; ?></span>
                            <span class="movimiento-datos">
                                <span class="movimiento-descripcion"><?php echo e($mov["descripcion"] !== "" ? $mov["descripcion"] : $mov["tipo"]); ?></span>
                                <span class="movimiento-meta">
                                    <?php echo e($mov["tipo"]); ?> · <?php echo e($mov["tipo_cuenta"]); ?> en <?php echo e($mov["moneda"]); ?> · <?php echo e(formatoFecha($mov["fecha"])); ?>
                                </span>
                            </span>
                            <span class="movimiento-monto <?php echo $ingreso ? "positivo" : "negativo"; ?>">
                                <?php echo e(formatoMonto($mov["monto"], $mov["moneda"])); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
