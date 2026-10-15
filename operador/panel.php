<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_OPERADOR]);

$movimientosHoy = consultarUno($conexion, "SELECT COUNT(*) AS total FROM movimientos WHERE DATE(fecha) = CURDATE()");
$clientesNuevos = consultarUno(
    $conexion,
    "SELECT COUNT(*) AS total FROM usuarios WHERE id_rol = ? AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
    "i",
    [ROL_CLIENTE]
);

$titulo = "Panel del operador";
require_once(__DIR__ . "/../includes/header.php");

?>

<h1>Panel del operador</h1>
<p class="texto-suave">Hola, <?php echo e($_SESSION["nombre"]); ?>. ¿Qué querés hacer hoy?</p>

<section class="grilla">
    <div class="tarjeta estadistica">
        <span class="valor"><?php echo (int) $movimientosHoy["total"]; ?></span>
        <span class="etiqueta">Movimientos registrados hoy</span>
    </div>
    <div class="tarjeta estadistica">
        <span class="valor"><?php echo (int) $clientesNuevos["total"]; ?></span>
        <span class="etiqueta">Clientes nuevos (últimos 30 días)</span>
    </div>
</section>

<nav class="menu-panel">
    <a href="alta_cliente.php"><strong>Alta de cliente</strong>Registrar un cliente nuevo con contraseña provisoria.</a>
    <a href="../admin/usuarios.php"><strong>Buscar clientes</strong>Por apellido, DNI o email.</a>
    <a href="../admin/movimientos.php"><strong>Depósitos y extracciones</strong>Buscar una cuenta y registrar operaciones.</a>
</nav>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
