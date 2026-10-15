<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

$idCuenta = isset($_GET["id_cuenta"]) ? (int) $_GET["id_cuenta"] : 0;

// No confiamos en el id de la URL: la cuenta tiene que pertenecer al
// usuario logueado. Si no, es como si no existiera.
$cuenta = consultarUno(
    $conexion,
    "SELECT * FROM cuentas WHERE id_cuenta = ? AND id_usuario = ?",
    "ii",
    [$idCuenta, $_SESSION["id_usuario"]]
);

if ($cuenta === null) {
    redirigir("cliente/panel.php?error=cuenta");
}

// ---------- Buscador ----------
$texto = campo($_GET, "texto");
$desde = campo($_GET, "desde");
$hasta = campo($_GET, "hasta");

$movimientos = buscarMovimientos($conexion, $idCuenta, $texto, $desde, $hasta);
$hayFiltros = $texto !== "" || $desde !== "" || $hasta !== "";

$titulo = "Movimientos";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <div>
        <h1>Movimientos</h1>
        <p class="texto-suave">
            <?php echo e($cuenta["tipo"]); ?> en <?php echo e($cuenta["moneda"]); ?> ·
            N.º <?php echo e($cuenta["numero_cuenta"]); ?> ·
            Saldo: <strong><?php echo e(formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?></strong>
        </p>
    </div>
    <a href="panel.php" class="boton boton-secundario">← Volver a mis cuentas</a>
</div>

<section class="tarjeta">
    <form method="GET" class="filtros">
        <input type="hidden" name="id_cuenta" value="<?php echo (int) $idCuenta; ?>">
        <div class="campo">
            <label for="texto">Descripción</label>
            <input type="search" id="texto" name="texto" placeholder="Ej.: sueldo" value="<?php echo e($texto); ?>">
        </div>
        <div class="campo">
            <label for="desde">Desde</label>
            <input type="date" id="desde" name="desde" value="<?php echo e($desde); ?>">
        </div>
        <div class="campo">
            <label for="hasta">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="<?php echo e($hasta); ?>">
        </div>
        <div class="acciones">
            <button type="submit" class="boton">Buscar</button>
            <?php if ($hayFiltros): ?>
                <a href="movimientos.php?id_cuenta=<?php echo (int) $idCuenta; ?>" class="boton boton-secundario">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th class="texto-derecha">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) === 0): ?>
                    <tr><td colspan="4" class="vacio"><?php echo $hayFiltros ? "No hay movimientos que coincidan con la búsqueda." : "Esta cuenta todavía no tiene movimientos."; ?></td></tr>
                <?php endif; ?>
                <?php foreach ($movimientos as $mov): ?>
                    <tr>
                        <td><?php echo e(formatoFecha($mov["fecha"])); ?></td>
                        <td><?php echo e($mov["tipo"]); ?></td>
                        <td><?php echo e($mov["descripcion"]); ?></td>
                        <td class="texto-derecha <?php echo $mov["monto"] < 0 ? "negativo" : "positivo"; ?>">
                            <?php echo e(formatoMonto($mov["monto"], $cuenta["moneda"])); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="texto-suave"><?php echo count($movimientos); ?> movimiento(s).</p>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
