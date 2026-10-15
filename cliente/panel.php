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
    <div class="tarjeta vacio">No tenés cuentas activas. Acercate a una sucursal para abrir una.</div>
<?php else: ?>
    <div class="tarjeta tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Moneda</th>
                    <th class="texto-derecha">Saldo</th>
                    <th>Apertura</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cuentas as $cuenta): ?>
                    <tr>
                        <td><?php echo e($cuenta["numero_cuenta"]); ?></td>
                        <td><?php echo e($cuenta["tipo"]); ?></td>
                        <td><?php echo e($cuenta["moneda"]); ?></td>
                        <td class="texto-derecha"><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></td>
                        <td><?php echo e(formatoFecha($cuenta["fecha_apertura"], false)); ?></td>
                        <td><a href="movimientos.php?id_cuenta=<?php echo (int) $cuenta["id_cuenta"]; ?>">Movimientos</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
