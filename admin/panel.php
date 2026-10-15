<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

$clientes = consultarUno($conexion, "SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = ? AND activo = 1", "i", [ROL_CLIENTE]);
$cuentas = consultarUno($conexion, "SELECT COUNT(*) AS total FROM cuentas WHERE activa = 1");
$saldos = consultar($conexion, "SELECT moneda, SUM(saldo) AS total FROM cuentas WHERE activa = 1 GROUP BY moneda ORDER BY moneda");
$sinLeer = consultarUno($conexion, "SELECT COUNT(*) AS total FROM contactos WHERE leido = 0");

$ultimosMovimientos = consultar(
    $conexion,
    "SELECT m.*, c.numero_cuenta, c.moneda, u.nombre, u.apellido
     FROM movimientos m
     JOIN cuentas c ON c.id_cuenta = m.id_cuenta
     JOIN usuarios u ON u.id_usuario = c.id_usuario
     ORDER BY m.fecha DESC, m.id_movimiento DESC
     LIMIT 5"
);

$titulo = "Panel de administración";
require_once(__DIR__ . "/../includes/header.php");

?>

<h1>Panel de administración</h1>
<p class="texto-suave">Hola, <?php echo e($_SESSION["nombre"]); ?>. Este es el resumen del banco.</p>

<section class="grilla">
    <div class="tarjeta estadistica">
        <span class="valor"><?php echo (int) $clientes["total"]; ?></span>
        <span class="etiqueta">Clientes activos</span>
    </div>
    <div class="tarjeta estadistica">
        <span class="valor"><?php echo (int) $cuentas["total"]; ?></span>
        <span class="etiqueta">Cuentas activas</span>
    </div>
    <?php foreach ($saldos as $saldo): ?>
        <div class="tarjeta estadistica">
            <span class="valor"><?php echo e(formatoMonto($saldo["total"], $saldo["moneda"])); ?></span>
            <span class="etiqueta">Saldo total en <?php echo e($saldo["moneda"]); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="tarjeta estadistica">
        <span class="valor"><?php echo (int) $sinLeer["total"]; ?></span>
        <span class="etiqueta">Consultas sin leer</span>
    </div>
</section>

<h2>Gestión</h2>
<nav class="menu-panel">
    <a href="cuentas.php"><strong>Cuentas</strong>Alta, modificación, baja y búsqueda de cuentas.</a>
    <a href="movimientos.php"><strong>Movimientos</strong>Depósitos, extracciones y corrección de movimientos.</a>
    <a href="usuarios.php"><strong>Usuarios</strong>Clientes, operadores y administradores. Reseteo de contraseñas.</a>
    <a href="contactos.php"><strong>Consultas</strong>Mensajes recibidos desde el formulario de contacto.</a>
</nav>

<h2>Últimos movimientos</h2>
<div class="tarjeta tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr><th>Fecha</th><th>Cuenta</th><th>Titular</th><th>Tipo</th><th>Descripción</th><th class="texto-derecha">Monto</th></tr>
        </thead>
        <tbody>
            <?php foreach ($ultimosMovimientos as $mov): ?>
                <tr>
                    <td><?php echo e(formatoFecha($mov["fecha"])); ?></td>
                    <td><a href="movimientos.php?id_cuenta=<?php echo (int) $mov["id_cuenta"]; ?>"><?php echo e($mov["numero_cuenta"]); ?></a></td>
                    <td><?php echo e($mov["apellido"] . ", " . $mov["nombre"]); ?></td>
                    <td><?php echo e($mov["tipo"]); ?></td>
                    <td><?php echo e($mov["descripcion"]); ?></td>
                    <td class="texto-derecha <?php echo $mov["monto"] < 0 ? "negativo" : "positivo"; ?>"><?php echo e(formatoMonto($mov["monto"], $mov["moneda"])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
