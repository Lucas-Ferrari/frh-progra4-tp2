<?php

// Reservas del cliente: plata separada que rinde todos los días y se
// puede retirar cuando quiera (como las reservas de Mercado Pago).

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");
require_once(__DIR__ . "/../includes/inversiones.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

// Suma lo que rindieron las reservas desde la última vez (evaluación perezosa).
actualizarRendimientoReservas($conexion, $_SESSION["id_usuario"]);

$cuentas = consultar(
    $conexion,
    "SELECT * FROM cuentas WHERE id_usuario = ? AND activa = 1 ORDER BY fecha_apertura",
    "i",
    [$_SESSION["id_usuario"]]
);

$reservas = reservasDelUsuario($conexion, $_SESSION["id_usuario"]);

// Totales por moneda para el resumen de arriba (pesos y dólares no se suman entre sí).
$totales = [];
foreach ($reservas as $reserva) {
    $moneda = $reserva["moneda"];
    if (!isset($totales[$moneda])) {
        $totales[$moneda] = ["saldo" => 0.0, "rendimientos" => 0.0];
    }
    $totales[$moneda]["saldo"] += (float) $reserva["saldo"];
    $totales[$moneda]["rendimientos"] += (float) $reserva["rendimientos"];
}

$titulo = "Reservas";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1>Reservas</h1>
    <div class="acciones">
        <a href="plazos_fijos.php" class="boton boton-secundario">Plazo fijo</a>
        <a href="panel.php" class="boton boton-secundario">Volver al panel</a>
    </div>
</div>

<?php if (count($totales) > 0): ?>
    <!-- Resumen: una columna por moneda (pesos y dólares no se suman entre sí) -->
    <div class="tarjeta resumen-reservas">
        <?php foreach ($totales as $moneda => $total): ?>
            <div class="resumen-reservas-item">
                <span class="texto-suave">Total en reservas en <?php echo $moneda === "USD" ? "dólares" : "pesos"; ?></span>
                <span class="plazo-total"><?php echo e(formatoMonto($total["saldo"], $moneda)); ?></span>
                <span class="positivo">+ <?php echo e(formatoMonto($total["rendimientos"], $moneda)); ?> ganados</span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="inversion-layout">
    <section aria-labelledby="mis-reservas">
        <h2 id="mis-reservas">Mis reservas</h2>

        <?php if (count($reservas) === 0): ?>
            <div class="tarjeta vacio">Todavía no tenés reservas. Creá la primera y empezá a ganar todos los días.</div>
        <?php else: ?>
            <ul class="lista-inversiones">
                <?php foreach ($reservas as $reserva): ?>
                    <?php
                    $id = (int) $reserva["id_reserva"];
                    $tna = TNA_RESERVA[$reserva["moneda"]];
                    // Lo que rinde hoy: saldo × TNA / 365. Es una estimación para mostrar.
                    $porDia = (float) $reserva["saldo"] * $tna / 36500;
                    $meta = $reserva["meta"] !== null ? (float) $reserva["meta"] : null;
                    $avanceMeta = $meta ? min(100, round((float) $reserva["saldo"] * 100 / $meta)) : null;
                    ?>
                    <li class="tarjeta reserva">
                        <div class="plazo-cabecera">
                            <h3 class="reserva-nombre"><?php echo e($reserva["nombre"]); ?></h3>
                            <span class="texto-suave"><?php echo e(number_format($tna, 2, ",", ".")); ?>% TNA</span>
                        </div>

                        <span class="plazo-total"><?php echo e(formatoMonto($reserva["saldo"], $reserva["moneda"])); ?></span>
                        <span class="texto-suave">
                            Ganaste <span class="positivo"><?php echo e(formatoMonto($reserva["rendimientos"], $reserva["moneda"])); ?></span>
                            · hoy rinde ≈ <?php echo e(formatoMonto($porDia, $reserva["moneda"])); ?>
                        </span>

                        <?php if ($meta): ?>
                            <div class="barra" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo (int) $avanceMeta; ?>"
                                 aria-label="Avance hacia la meta de <?php echo e($reserva["nombre"]); ?>">
                                <span style="width: <?php echo (int) $avanceMeta; ?>%"></span>
                            </div>
                            <div class="plazo-pie">
                                <span><?php echo (int) $avanceMeta; ?>% de la meta</span>
                                <span>Meta: <?php echo e(formatoMonto($meta, $reserva["moneda"])); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Un solo campo de monto y dos botones: el que se toca define la acción -->
                        <form action="../procesos/procesar_reserva.php" method="POST" novalidate class="reserva-mover">
                            <input type="hidden" name="id_reserva" value="<?php echo $id; ?>">
                            <div class="campo">
                                <label for="monto-<?php echo $id; ?>">Monto</label>
                                <input type="number" id="monto-<?php echo $id; ?>" name="monto" inputmode="decimal" autocomplete="off" min="0.01" step="0.01" required data-monto>
                            </div>
                            <button type="submit" name="accion" value="ingresar" class="boton boton-chico">Ingresar</button>
                            <button type="submit" name="accion" value="retirar" class="boton boton-chico boton-secundario">Retirar</button>
                        </form>

                        <div class="reserva-pie">
                            <span class="texto-suave">Desde tu <?php echo e(strtolower($reserva["tipo_cuenta"]) . " en " . $reserva["moneda"]); ?></span>
                            <form action="../procesos/procesar_reserva.php" method="POST"
                                  data-confirmar="¿Cerrar la reserva &quot;<?php echo e($reserva["nombre"]); ?>&quot;? Todo lo que tiene vuelve a tu cuenta.">
                                <input type="hidden" name="accion" value="cerrar">
                                <input type="hidden" name="id_reserva" value="<?php echo $id; ?>">
                                <button type="submit" class="boton-texto">Cerrar reserva</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="tarjeta formulario" aria-labelledby="nueva-reserva">
        <h2 id="nueva-reserva">Nueva reserva</h2>
        <p class="texto-suave">
            Rinde <strong><?php echo e(number_format(TNA_RESERVA["ARS"], 2, ",", ".")); ?>% TNA</strong> en pesos y
            <strong><?php echo e(number_format(TNA_RESERVA["USD"], 2, ",", ".")); ?>% TNA</strong> en dólares, todos los días.
            Podés retirar cuando quieras.
        </p>

        <?php if (count($cuentas) === 0): ?>
            <p class="vacio">No tenés cuentas activas.</p>
        <?php else: ?>
            <form action="../procesos/procesar_reserva.php" method="POST" novalidate>
                <input type="hidden" name="accion" value="crear">

                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" autocomplete="off" maxlength="<?php echo RESERVA_NOMBRE_MAXIMO; ?>" required
                           placeholder="Ej.: Vacaciones, Auto, Emergencias" value="<?php echo e(valorAnterior("nombre")); ?>">
                </div>

                <div class="campo">
                    <label for="id_cuenta">Cuenta</label>
                    <select id="id_cuenta" name="id_cuenta" required>
                        <?php foreach ($cuentas as $cuenta): ?>
                            <option value="<?php echo (int) $cuenta["id_cuenta"]; ?>" <?php echo (int) valorAnterior("id_cuenta") === (int) $cuenta["id_cuenta"] ? "selected" : ""; ?>>
                                <?php echo e($cuenta["tipo"] . " " . $cuenta["moneda"] . " · " . formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="ayuda">La reserva queda en la moneda de esta cuenta.</span>
                </div>

                <div class="fila-campos">
                    <div class="campo">
                        <label for="monto-inicial">Monto inicial (opcional)</label>
                        <input type="number" id="monto-inicial" name="monto" inputmode="decimal" autocomplete="off" min="0.01" step="0.01" value="<?php echo e(valorAnterior("monto")); ?>">
                    </div>
                    <div class="campo">
                        <label for="meta">Meta (opcional)</label>
                        <input type="number" id="meta" name="meta" inputmode="decimal" autocomplete="off" min="0.01" step="0.01" value="<?php echo e(valorAnterior("meta")); ?>">
                    </div>
                </div>

                <div class="acciones">
                    <button type="submit" class="boton">Crear reserva</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
