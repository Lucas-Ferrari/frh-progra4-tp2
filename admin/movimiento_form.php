<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

$idMovimiento = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$movimiento = consultarUno(
    $conexion,
    "SELECT m.*, c.numero_cuenta, c.moneda, c.saldo, c.activa
     FROM movimientos m JOIN cuentas c ON c.id_cuenta = m.id_cuenta
     WHERE m.id_movimiento = ?",
    "i",
    [$idMovimiento]
);

if ($movimiento === null) {
    redirigir("admin/cuentas.php?error=no_encontrado");
}
if ($movimiento["tipo"] === "Transferencia" || !$movimiento["activa"]) {
    redirigir("admin/movimientos.php?id_cuenta=" . (int) $movimiento["id_cuenta"] . "&error=no_editable");
}

$tipo = valorAnterior("tipo", $movimiento["tipo"]);
$monto = valorAnterior("monto", number_format(abs((float) $movimiento["monto"]), 2, ".", ""));
$descripcion = valorAnterior("descripcion", $movimiento["descripcion"]);

$titulo = "Editar movimiento";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Editar movimiento</h1>
    <p class="texto-suave">
        Cuenta <?php echo e($movimiento["numero_cuenta"]); ?> ·
        <?php echo e(formatoFecha($movimiento["fecha"])); ?> ·
        Saldo actual: <?php echo e(formatoMonto($movimiento["saldo"], $movimiento["moneda"])); ?>
    </p>

    <form action="../procesos/procesar_movimiento.php" method="POST" novalidate>
        <input type="hidden" name="accion" value="modificar">
        <input type="hidden" name="id_movimiento" value="<?php echo (int) $idMovimiento; ?>">

        <div class="fila-campos">
            <div class="campo">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="Depósito" <?php echo $tipo === "Depósito" ? "selected" : ""; ?>>Depósito</option>
                    <option value="Extracción" <?php echo $tipo === "Extracción" ? "selected" : ""; ?>>Extracción</option>
                </select>
            </div>
            <div class="campo">
                <label for="monto">Monto (<?php echo e($movimiento["moneda"]); ?>)</label>
                <input type="number" id="monto" name="monto" min="0.01" step="0.01" required data-monto value="<?php echo e($monto); ?>">
            </div>
        </div>

        <div class="campo">
            <label for="descripcion">Descripción</label>
            <input type="text" id="descripcion" name="descripcion" maxlength="150" value="<?php echo e($descripcion); ?>">
        </div>

        <p class="texto-suave">Al guardar, el saldo de la cuenta se recalcula automáticamente.</p>

        <div class="acciones">
            <button type="submit" class="boton">Guardar cambios</button>
            <a href="movimientos.php?id_cuenta=<?php echo (int) $movimiento["id_cuenta"]; ?>" class="boton boton-secundario">Cancelar</a>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
