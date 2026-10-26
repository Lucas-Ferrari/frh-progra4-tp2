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

<h2>Mis cuentas</h2>

<?php if (count($cuentas) === 0): ?>
    <div class="tarjeta vacio">No tenés cuentas activas. Acercate a la sucursal para abrir una.</div>
<?php else: ?>
    <div class="cuentas">
        <?php foreach ($cuentas as $cuenta): ?>
            <article class="cuenta">
                <span class="cuenta-tipo"><?php echo e(ucfirst($cuenta["tipo"])); ?> en <?php echo e($cuenta["moneda"]); ?></span>
                <span class="cuenta-numero">N.º <?php echo e($cuenta["numero_cuenta"]); ?></span>
                <span class="monto"><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></span>
                <span class="texto-suave">Saldo disponible</span>
                <div class="cuenta-pie">
                    <span>Desde <?php echo e(formatoFecha($cuenta["fecha_apertura"], false)); ?></span>
                    <a href="movimientos.php?id_cuenta=<?php echo (int) $cuenta["id_cuenta"]; ?>">Ver movimientos</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
