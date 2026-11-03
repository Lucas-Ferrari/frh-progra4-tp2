<?php

$titulo = "Inicio";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="portada">
    <div>
        <h1>Tu plata, simple y a mano.</h1>
        <p>
            Tu banco digital: abrí tu cuenta en minutos, consultá tus saldos y movimientos
            y transferí dinero las 24 horas, desde cualquier dispositivo.
        </p>
        <div class="acciones">
            <?php if (estaLogueado()): ?>
                <a href="<?php echo e(panelSegunRol(rolActual())); ?>" class="boton">Ir a mi panel</a>
            <?php else: ?>
                <a href="registro.php" class="boton boton-cta">
                    Registrate gratis
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <span class="cta-nota">Sin costo de mantenimiento · Lista en minutos</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ilustración del producto: datos de ejemplo, no son cuentas reales -->
    <div class="portada-visual" aria-hidden="true">
        <div class="muestra-tarjeta">
            <div class="marca-chip"></div>
            <div class="numero">•••• •••• •••• 2264</div>
            <div class="pie-tarjeta"><span>Future Bank</span><span>Débito</span></div>
        </div>
        <div class="muestra-saldo">
            <span class="etiqueta">Caja de ahorro en pesos</span>
            <span class="valor">$ 245.380,50</span>
            <div class="movimiento"><span>Transferencia recibida</span><span class="positivo">+ $ 18.000,00</span></div>
        </div>
    </div>
</section>

<!-- Texto a la izquierda e ilustración a la derecha: la sección usa todo el ancho -->
<section class="tarjeta quienes">
    <div>
        <h2>Quiénes somos</h2>
        <p>
            Future Bank nació en 2010 con una idea simple: que manejar tu dinero sea fácil,
            transparente y sin costos ocultos. Hoy acompañamos a miles de personas y
            pequeñas empresas con atención personalizada en nuestra sucursal y una plataforma
            online disponible todo el día.
        </p>
    </div>
    <figure class="quienes-imagen">
        <!-- width/height: el navegador reserva el espacio antes de cargarla (evita saltos de diseño) -->
        <img src="img/SucursalCorrientes.jpg" width="2752" height="1536" loading="lazy"
             alt="Sucursal de Future Bank sobre la Avenida Corrientes de noche, con el Obelisco al fondo">
        <figcaption>Nuestra casa central, en Av. Corrientes 1234.</figcaption>
    </figure>
</section>

<h2 class="seccion-titulo">Nuestros productos</h2>

<!-- El plazo fijo va destacado: la invitación a abrir la cuenta ya está en la banda de abajo.
     El resto, en una lista simple con el ícono al lado del título. -->
<section class="productos">
    <article class="producto-destacado">
        <!-- Línea de crecimiento decorativa de fondo -->
        <svg class="producto-curva" viewBox="0 0 400 160" preserveAspectRatio="none" aria-hidden="true">
            <defs>
                <linearGradient id="curva-relleno" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#8a63ff" stop-opacity="0.35"/>
                    <stop offset="1" stop-color="#8a63ff" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path d="M0 140 C 60 132, 90 118, 140 112 S 230 84, 280 66 S 360 22, 400 12 V160 H0 Z" fill="url(#curva-relleno)"/>
            <path d="M0 140 C 60 132, 90 118, 140 112 S 230 84, 280 66 S 360 22, 400 12" fill="none" stroke="#a88bff" stroke-width="2" vector-effect="non-scaling-stroke"/>
        </svg>

        <div class="producto-cabecera">
            <span class="producto-etiqueta">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
                Destacado
            </span>
            <h3>Plazo fijo</h3>
            <p>Hacé crecer tus ahorros con una tasa fija, en pesos o en dólares.</p>
        </div>

        <!-- Tasa de ejemplo: el banco es ficticio -->
        <p class="producto-tasa">
            <span class="producto-tasa-valor">32<small>%</small></span>
            <span class="producto-tasa-label">TNA</span>
        </p>

        <ul class="producto-datos">
            <li>Desde 30 días</li>
            <li>ARS · USD</li>
        </ul>

        <a href="contacto.php" class="boton">
            Consultá con un asesor
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
        </a>
    </article>
    <ul class="productos-lista">
        <li>
            <h3>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
                Caja de ahorro
            </h3>
            <p>En pesos y en dólares, sin costo de mantenimiento. Se abre automáticamente al registrarte.</p>
        </li>
        <li>
            <h3>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 6-6"/></svg>
                Cuenta corriente
            </h3>
            <p>Ideal para profesionales y comercios: cobrá, pagá a proveedores y gestioná tus cheques.</p>
        </li>
        <li>
            <h3>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
                Tarjetas
            </h3>
            <p>Débito incluida con tu cuenta y tarjetas de crédito con cuotas sin interés en comercios adheridos.</p>
        </li>
        <li>
            <h3>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3l4 4-4 4"/><path d="M21 7H7"/><path d="M7 21l-4-4 4-4"/><path d="M3 17h14"/></svg>
                Transferencias
            </h3>
            <p>Transferí al instante entre tus cuentas o a cualquier cuenta del banco, las 24 horas.</p>
        </li>
    </ul>
</section>

<?php if (!estaLogueado()): ?>
    <!-- Misma acción que el hero, después de conocer los productos -->
    <section class="banda-cta">
        <div>
            <h2>Abrí tu cuenta en minutos</h2>
            <p>Sin costo de mantenimiento y con todos los descuentos de abajo.</p>
        </div>
        <a href="registro.php" class="boton">Registrate gratis</a>
    </section>
<?php endif; ?>

<?php
// Promos de ejemplo: % y días inventados (el banco es ficticio). El logo se busca en
// svg/<logo>.svg (o .png si la marca no publica vector); si no está, se muestran las iniciales en el color de la marca.
$descuentos = [
    ["comercio" => "Carrefour", "logo" => "carrefour", "color" => "#004e9f", "rubro" => "Supermercados", "porcentaje" => 25, "dia" => "Miércoles", "tope" => 15000],
    ["comercio" => "Coto", "logo" => "coto", "color" => "#e30613", "rubro" => "Supermercados", "porcentaje" => 20, "dia" => "Jueves", "tope" => 12000],
    ["comercio" => "YPF", "logo" => "ypf", "color" => "#0033a0", "rubro" => "Combustible", "porcentaje" => 15, "dia" => "Lunes", "tope" => 8000],
    ["comercio" => "Shell", "logo" => "shell", "color" => "#dd1d21", "rubro" => "Combustible", "porcentaje" => 15, "dia" => "Domingo", "tope" => 8000],
    ["comercio" => "Farmacity", "logo" => "farmacity", "color" => "#0059a6", "rubro" => "Farmacias", "porcentaje" => 20, "dia" => "Martes", "tope" => 6000],
    ["comercio" => "Cinemark Hoyts", "logo" => "cinemark", "color" => "#b5121b", "rubro" => "Entretenimiento", "porcentaje" => 50, "dia" => "Miércoles", "tope" => 5000],
    ["comercio" => "Freddo", "logo" => "freddo", "color" => "#5b2c1d", "rubro" => "Gastronomía", "porcentaje" => 30, "dia" => "Viernes", "tope" => 4000],
    ["comercio" => "Musimundo", "logo" => "musimundo", "color" => "#e4002b", "rubro" => "Tecnología", "porcentaje" => 15, "dia" => "Sábado", "tope" => 20000],
];

function logoDescuento($d)
{
    foreach (["svg", "png"] as $ext) {
        $archivo = "svg/" . $d["logo"] . "." . $ext;
        if (file_exists(__DIR__ . "/" . $archivo)) {
            // alt vacío: el nombre del comercio ya está escrito en la tarjeta
            return '<img src="' . e($archivo) . '" alt="" width="176" height="64" loading="lazy">';
        }
    }
    $iniciales = mb_substr($d["comercio"], 0, 2);
    return '<span class="descuento-iniciales" style="background:' . e($d["color"]) . '">' . e($iniciales) . '</span>';
}

// Una tarjeta. $copia: el segundo juego del bucle infinito (oculto a lectores de pantalla y al Tab)
function tarjetaDescuento($d, $indice, $copia)
{
    $extra = $copia ? ' tabindex="-1"' : '';
    return '<li' . ($copia ? ' aria-hidden="true"' : '') . '>
        <button type="button" class="descuento" data-promo="' . $indice . '"' . $extra . '>
            <span class="descuento-logo">' . logoDescuento($d) . '</span>
            <span class="descuento-dia">' . e($d["dia"]) . '</span>
            <span class="descuento-porcentaje">' . e($d["porcentaje"]) . '%</span>
            <span class="descuento-comercio">' . e($d["comercio"]) . '</span>
            <span class="descuento-rubro">' . e($d["rubro"]) . '</span>
        </button>
    </li>';
}
?>

<section class="descuentos" aria-labelledby="descuentos-titulo">
    <div class="descuentos-encabezado">
        <h2 id="descuentos-titulo" class="seccion-titulo">Descuentos de la semana</h2>
        <div class="descuentos-flechas">
            <button type="button" class="carrusel-flecha" data-carrusel="-1" aria-label="Descuentos anteriores">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            </button>
            <button type="button" class="carrusel-flecha" data-carrusel="1" aria-label="Descuentos siguientes">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </div>
    </div>

    <!-- Bucle infinito: la lista va dos veces y portada.js vuelve al inicio al pasar la mitad -->
    <ul class="carrusel" aria-label="Comercios con descuento">
        <?php foreach ($descuentos as $i => $d) { echo tarjetaDescuento($d, $i, false); } ?>
        <?php foreach ($descuentos as $i => $d) { echo tarjetaDescuento($d, $i, true); } ?>
    </ul>

    <!-- Detalle de la promo: <dialog> nativo (Esc y foco atrapado los resuelve el navegador) -->
    <dialog class="promo-modal" aria-labelledby="promo-titulo">
        <button type="button" class="promo-cerrar" aria-label="Cerrar">&times;</button>
        <span class="descuento-logo" data-campo="logo"></span>
        <p class="descuento-porcentaje"><span data-campo="porcentaje"></span>% de descuento</p>
        <h3 id="promo-titulo" data-campo="comercio"></h3>
        <dl class="promo-datos">
            <div><dt>Día</dt><dd>Todos los <span data-campo="dia"></span></dd></div>
            <div><dt>Rubro</dt><dd data-campo="rubro"></dd></div>
            <div><dt>Tope de reintegro</dt><dd>$ <span data-campo="tope"></span> por mes</dd></div>
            <div><dt>Medio de pago</dt><dd>Tarjetas de débito y crédito Future Bank</dd></div>
        </dl>
        <p class="promo-legal">El reintegro se acredita en tu cuenta dentro de los 10 días hábiles. Promoción de ejemplo.</p>
        <?php if (!estaLogueado()): ?>
            <a href="registro.php" class="boton">Registrate y aprovechala</a>
        <?php endif; ?>
    </dialog>
    <script>window.PROMOS = <?php echo json_encode(array_map(function ($d) {
        return ["comercio" => $d["comercio"], "rubro" => $d["rubro"], "porcentaje" => $d["porcentaje"],
            // "Todos los lunes / sábados": los días que no terminan en s suman una
            "dia" => mb_strtolower($d["dia"]) . (substr($d["dia"], -1) === "s" ? "" : "s"),
            "tope" => number_format($d["tope"], 0, ",", "."), "logo" => logoDescuento($d)];
    }, $descuentos), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;</script>
</section>

<section class="banda-cta banda-contacto">
    <div>
        <h2>¿Tenés dudas?</h2>
        <p>Escribinos desde el formulario de contacto o acercate a nuestra sucursal.</p>
    </div>
    <a href="contacto.php" class="boton">Ir al formulario de contacto</a>
</section>

<!-- Animación de la portada: solo la usa esta página, por eso se carga acá
     y no en el header de todo el sitio. ?v= evita que quede en caché. -->
<script src="js/portada.js?v=<?php echo filemtime(__DIR__ . "/js/portada.js"); ?>" defer></script>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
