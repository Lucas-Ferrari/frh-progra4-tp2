<?php

// Plazos fijos del cliente: simulador + alta a la izquierda, sus plazos a la derecha.

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");
require_once(__DIR__ . "/../includes/inversiones.php");

requerirPasswordActualizada();
requerirRol([ROL_CLIENTE]);

// Antes de mostrar nada se acreditan los que ya vencieron (evaluación perezosa).
acreditarPlazosVencidos($conexion, $_SESSION["id_usuario"]);

$cuentas = consultar(
    $conexion,
    "SELECT * FROM cuentas WHERE id_usuario = ? AND activa = 1 ORDER BY fecha_apertura",
    "i",
    [$_SESSION["id_usuario"]]
);

$plazos = plazosFijosDelUsuario($conexion, $_SESSION["id_usuario"]);
$activos = array_filter($plazos, function ($p) { return $p["estado"] === "Activo"; });
$acreditados = array_filter($plazos, function ($p) { return $p["estado"] === "Acreditado"; });

$diasElegidos = (int) valorAnterior("dias", 30);

$titulo = "Plazo fijo";
require_once(__DIR__ . "/../includes/header.php");

?>

<div class="encabezado-seccion">
    <h1>Plazo fijo</h1>
    <div class="acciones">
        <a href="reservas.php" class="boton boton-secundario">Reservas</a>
        <a href="panel.php" class="boton boton-secundario">Volver al panel</a>
    </div>
</div>

<div class="inversion-layout">
    <section class="tarjeta formulario" aria-labelledby="nuevo-plazo">
        <h2 id="nuevo-plazo">Nuevo plazo fijo</h2>
        <p class="texto-suave">
            Tasa fija: <strong><?php echo e(number_format(TNA_PLAZO_FIJO["ARS"], 2, ",", ".")); ?>% TNA</strong> en pesos y
            <strong><?php echo e(number_format(TNA_PLAZO_FIJO["USD"], 2, ",", ".")); ?>% TNA</strong> en dólares.
            La plata queda inmovilizada hasta el vencimiento.
        </p>

        <?php if (count($cuentas) === 0): ?>
            <p class="vacio">No tenés cuentas activas.</p>
        <?php else: ?>
            <!-- data-*: tasas y mínimos para el simulador de js/inversiones.js.
                 El servidor vuelve a calcular todo: el simulador es solo una ayuda. -->
            <form action="../procesos/procesar_plazo_fijo.php" method="POST" novalidate class="simulador-plazo"
                  data-tna='<?php echo e(json_encode(TNA_PLAZO_FIJO)); ?>'
                  data-confirmar="¿Constituir el plazo fijo? La plata queda inmovilizada hasta el vencimiento.">
                <div class="campo">
                    <label for="id_cuenta">Cuenta de origen</label>
                    <select id="id_cuenta" name="id_cuenta" required>
                        <?php foreach ($cuentas as $cuenta): ?>
                            <option value="<?php echo (int) $cuenta["id_cuenta"]; ?>"
                                    data-moneda="<?php echo e($cuenta["moneda"]); ?>"
                                    <?php echo (int) valorAnterior("id_cuenta") === (int) $cuenta["id_cuenta"] ? "selected" : ""; ?>>
                                <?php echo e($cuenta["tipo"] . " " . $cuenta["moneda"] . " · " . formatoMonto($cuenta["saldo"], $cuenta["moneda"])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="monto">Monto a invertir</label>
                    <input type="number" id="monto" name="monto" inputmode="decimal" autocomplete="off" min="0.01" step="0.01" required data-monto value="<?php echo e(valorAnterior("monto")); ?>">
                    <span class="ayuda">Mínimo $ 1.000 en pesos o US$ 100 en dólares.</span>
                </div>

                <fieldset class="campo">
                    <legend>Plazo</legend>
                    <div class="opciones-radio opciones-plazo">
                        <?php foreach (PLAZO_FIJO_DIAS as $dias): ?>
                            <label>
                                <input type="radio" name="dias" value="<?php echo $dias; ?>" <?php echo $dias === $diasElegidos ? "checked" : ""; ?>>
                                <?php echo $dias; ?> días
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <!-- Resultado del simulador: aria-live para que el lector de pantalla
                     anuncie el nuevo total cada vez que cambia el monto o el plazo -->
                <div class="simulacion" aria-live="polite">
                    <span class="simulacion-etiqueta">Cobrás al vencimiento</span>
                    <strong class="simulacion-total" data-sim="total">—</strong>
                    <dl class="simulacion-detalle">
                        <div><dt>Interés</dt><dd data-sim="interes">—</dd></div>
                        <div><dt>Vence</dt><dd data-sim="vencimiento">—</dd></div>
                        <div><dt>TNA</dt><dd data-sim="tna">—</dd></div>
                    </dl>
                </div>

                <div class="acciones">
                    <button type="submit" class="boton">Constituir plazo fijo</button>
                </div>
            </form>
        <?php endif; ?>
    </section>

    <section aria-labelledby="mis-plazos">
        <h2 id="mis-plazos">Mis plazos fijos</h2>

        <?php if (count($activos) === 0): ?>
            <div class="tarjeta vacio">No tenés plazos fijos activos.</div>
        <?php else: ?>
            <ul class="lista-inversiones">
                <?php foreach ($activos as $plazo): ?>
                    <?php
                    $total = (float) $plazo["capital"] + (float) $plazo["interes"];
                    $transcurridos = (int) $plazo["dias"] - (int) $plazo["dias_restantes"];
                    $avance = min(100, max(0, round($transcurridos * 100 / (int) $plazo["dias"])));
                    ?>
                    <li class="tarjeta plazo">
                        <div class="plazo-cabecera">
                            <span class="etiqueta-estado">A <?php echo (int) $plazo["dias"]; ?> días</span>
                            <span class="texto-suave"><?php echo e(number_format((float) $plazo["tna"], 2, ",", ".")); ?>% TNA</span>
                        </div>
                        <span class="plazo-total"><?php echo e(formatoMonto($total, $plazo["moneda"])); ?></span>
                        <span class="texto-suave">
                            <?php echo e(formatoMonto($plazo["capital"], $plazo["moneda"])); ?> +
                            <span class="positivo"><?php echo e(formatoMonto($plazo["interes"], $plazo["moneda"])); ?> de interés</span>
                        </span>

                        <!-- Barra de avance: días transcurridos sobre el total del plazo -->
                        <div class="barra" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo (int) $avance; ?>"
                             aria-label="Avance del plazo fijo">
                            <span style="width: <?php echo (int) $avance; ?>%"></span>
                        </div>
                        <div class="plazo-pie">
                            <span>Vence el <?php echo e(formatoFecha($plazo["fecha_vencimiento"], false)); ?></span>
                            <span><?php echo (int) $plazo["dias_restantes"] === 1 ? "Falta 1 día" : "Faltan " . (int) $plazo["dias_restantes"] . " días"; ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (count($acreditados) > 0): ?>
            <h3 class="subtitulo-historial">Acreditados</h3>
            <div class="tarjeta ultimos-movimientos">
                <ul class="lista-movimientos">
                    <?php foreach ($acreditados as $plazo): ?>
                        <li>
                            <span class="movimiento-icono ingreso" aria-hidden="true">↓</span>
                            <span class="movimiento-datos">
                                <span class="movimiento-descripcion">Plazo fijo a <?php echo (int) $plazo["dias"]; ?> días</span>
                                <span class="movimiento-meta">
                                    <?php echo e(formatoMonto($plazo["capital"], $plazo["moneda"])); ?> al <?php echo e(number_format((float) $plazo["tna"], 2, ",", ".")); ?>% ·
                                    acreditado el <?php echo e(formatoFecha($plazo["fecha_acreditacion"], false)); ?>
                                </span>
                            </span>
                            <span class="movimiento-monto positivo">+ <?php echo e(formatoMonto($plazo["interes"], $plazo["moneda"])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="../js/inversiones.js?v=<?php echo filemtime(__DIR__ . "/../js/inversiones.js"); ?>" defer></script>

<?php require_once(__DIR__ . "/../includes/footer.php"); ?>
