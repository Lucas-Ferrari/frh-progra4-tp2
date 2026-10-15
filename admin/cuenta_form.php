<?php

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

// Con ?id=X es una modificación; sin id, un alta.
$idCuenta = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$cuenta = null;
$tieneMovimientos = false;

if ($idCuenta > 0) {
    $cuenta = consultarUno(
        $conexion,
        "SELECT c.*, u.nombre, u.apellido, u.dni,
                (SELECT COUNT(*) FROM movimientos m WHERE m.id_cuenta = c.id_cuenta) AS cantidad_movimientos
         FROM cuentas c JOIN usuarios u ON u.id_usuario = c.id_usuario
         WHERE c.id_cuenta = ? AND c.activa = 1",
        "i",
        [$idCuenta]
    );

    if ($cuenta === null) {
        redirigir("admin/cuentas.php?error=no_encontrado");
    }

    $tieneMovimientos = $cuenta["cantidad_movimientos"] > 0;
}

$esAlta = $cuenta === null;

// Para el alta: lista de clientes activos a los que se les puede abrir una cuenta.
$clientes = $esAlta
    ? consultar($conexion, "SELECT id_usuario, nombre, apellido, dni FROM usuarios WHERE id_rol = ? AND activo = 1 ORDER BY apellido, nombre", "i", [ROL_CLIENTE])
    : [];

$tipoSeleccionado = valorAnterior("tipo", $esAlta ? "Caja de ahorro" : $cuenta["tipo"]);
$monedaSeleccionada = valorAnterior("moneda", $esAlta ? "ARS" : $cuenta["moneda"]);
$clienteSeleccionado = (int) valorAnterior("id_usuario", isset($_GET["id_usuario"]) ? (int) $_GET["id_usuario"] : 0);

$titulo = $esAlta ? "Nueva cuenta" : "Editar cuenta";
require_once(__DIR__ . "/../includes/header.php");

?>

<section class="tarjeta formulario formulario-centrado">
    <h1><?php echo $esAlta ? "Nueva cuenta" : "Editar cuenta " . e($cuenta["numero_cuenta"]); ?></h1>

    <form action="../procesos/procesar_cuenta.php" method="POST" novalidate>
        <input type="hidden" name="accion" value="<?php echo $esAlta ? "alta" : "modificar"; ?>">

        <?php if ($esAlta): ?>
            <div class="campo">
                <label for="id_usuario">Cliente titular</label>
                <select id="id_usuario" name="id_usuario" required>
                    <option value="">Elegí un cliente…</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo (int) $cliente["id_usuario"]; ?>" <?php echo $clienteSeleccionado === (int) $cliente["id_usuario"] ? "selected" : ""; ?>>
                            <?php echo e($cliente["apellido"] . ", " . $cliente["nombre"] . " (DNI " . $cliente["dni"] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php else: ?>
            <input type="hidden" name="id_cuenta" value="<?php echo (int) $cuenta["id_cuenta"]; ?>">
            <div class="campo">
                <label for="titular">Titular</label>
                <input type="text" id="titular" value="<?php echo e($cuenta["apellido"] . ", " . $cuenta["nombre"] . " (DNI " . $cuenta["dni"] . ")"); ?>" disabled>
            </div>
        <?php endif; ?>

        <div class="fila-campos">
            <div class="campo">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" required>
                    <?php foreach (TIPOS_CUENTA as $tipo): ?>
                        <option value="<?php echo e($tipo); ?>" <?php echo $tipoSeleccionado === $tipo ? "selected" : ""; ?>><?php echo e($tipo); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="moneda">Moneda</label>
                <select id="moneda" name="moneda" required <?php echo $tieneMovimientos ? "disabled" : ""; ?>>
                    <?php foreach (MONEDAS as $moneda): ?>
                        <option value="<?php echo e($moneda); ?>" <?php echo $monedaSeleccionada === $moneda ? "selected" : ""; ?>><?php echo e($moneda); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($tieneMovimientos): ?>
                    <span class="ayuda">No se puede cambiar la moneda de una cuenta con movimientos.</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($esAlta): ?>
            <div class="campo">
                <label for="saldo_inicial">Saldo inicial</label>
                <input type="number" id="saldo_inicial" name="saldo_inicial" min="0" step="0.01" value="<?php echo e(valorAnterior("saldo_inicial", "0")); ?>">
                <span class="ayuda">Si es mayor a 0 se registra como un depósito "Saldo inicial".</span>
            </div>
        <?php endif; ?>

        <div class="acciones">
            <button type="submit" class="boton"><?php echo $esAlta ? "Crear cuenta" : "Guardar cambios"; ?></button>
            <a href="cuentas.php" class="boton boton-secundario">Cancelar</a>
        </div>
    </form>
</section>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
