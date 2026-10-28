<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

$cuentas = consultar(
    $conexion,
    "SELECT * FROM cuentas WHERE id_usuario = ? AND activa = 1 ORDER BY fecha_apertura",
    "i",
    [$_SESSION["id_usuario"]]
);

$titulo = "Transferir";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1>Transferir</h1>
    <p class="texto-suave">Transferí entre tus cuentas o a cualquier cuenta de Banco Andino en la misma moneda.</p>

    <?php if (count($cuentas) === 0): ?>
        <p>No tenés cuentas activas desde las cuales transferir.</p>
    <?php else: ?>
        <form action="../procesos/procesar_transferencia.php" method="POST" novalidate>
            <div class="campo">
                <label for="id_cuenta_origen">Cuenta de origen</label>
                <select id="id_cuenta_origen" name="id_cuenta_origen" required>
                    <?php foreach ($cuentas as $cuenta): ?>
                        <option value="<?php echo (int) $cuenta["id_cuenta"]; ?>" <?php echo (int) valorAnterior("id_cuenta_origen") === (int) $cuenta["id_cuenta"] ? "selected" : ""; ?>>
                            <?php echo e($cuenta["numero_cuenta"] . " — " . $cuenta["tipo"] . " " . $cuenta["moneda"] . " (" . formatoMonto($cuenta["saldo"], $cuenta["moneda"]) . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="numero_destino">Número de cuenta destino</label>
                <input type="text" id="numero_destino" name="numero_destino" autocomplete="off" inputmode="numeric" maxlength="20" required list="mis-cuentas" value="<?php echo e(valorAnterior("numero_destino")); ?>">
                <datalist id="mis-cuentas">
                    <?php foreach ($cuentas as $cuenta): ?>
                        <option value="<?php echo e($cuenta["numero_cuenta"]); ?>"><?php echo e($cuenta["tipo"] . " " . $cuenta["moneda"]); ?></option>
                    <?php endforeach; ?>
                </datalist>
                <span class="ayuda">Podés elegir una de tus cuentas o escribir el número de otra.</span>
            </div>

            <div class="campo">
                <label for="monto">Monto</label>
                <input type="number" id="monto" name="monto" inputmode="decimal" autocomplete="off" min="0.01" step="0.01" required data-monto value="<?php echo e(valorAnterior("monto")); ?>">
            </div>

            <div class="campo">
                <label for="descripcion">Concepto (opcional)</label>
                <input type="text" id="descripcion" name="descripcion" autocomplete="off" maxlength="100" value="<?php echo e(valorAnterior("descripcion")); ?>">
            </div>

            <div class="acciones">
                <button type="submit" class="boton">Transferir</button>
                <a href="panel.php" class="boton boton-secundario">Cancelar</a>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
