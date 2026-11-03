<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

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
