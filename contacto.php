<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si hay sesión, pre-cargamos nombre y email del usuario.
$nombrePorDefecto = "";
$emailPorDefecto = "";

if (estaLogueado()) {
    $nombrePorDefecto = $_SESSION["nombre"] . " " . $_SESSION["apellido"];
    $emailPorDefecto = $_SESSION["email"];
}

// Estado de la sucursal en hora argentina: lunes a viernes de 10 a 15 h.
$ahora = new DateTime("now", new DateTimeZone("America/Argentina/Buenos_Aires"));
$diaHabil = (int) $ahora->format("N") <= 5;
$hora = (int) $ahora->format("G");
$abierto = $diaHabil && $hora >= 10 && $hora < 15;
if ($abierto) {
    $estadoTexto = "Abierto ahora, atendemos hasta las 15 h";
} elseif ($diaHabil && $hora < 10) {
    $estadoTexto = "Cerrado, abrimos hoy a las 10 h";
} else {
    $estadoTexto = "Cerrado, abrimos el próximo día hábil a las 10 h";
}

$titulo = "Contacto";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="contacto-hero">
    <div>
        <h1>Hablemos.</h1>
        <p>Una duda con tu cuenta, una transferencia que no llegó o una idea para mejorar el banco: te respondemos en menos de 48 h hábiles.</p>
    </div>
    <p class="estado-sucursal <?php echo $abierto ? "abierto" : "cerrado"; ?>"><span aria-hidden="true"></span><?php echo e($estadoTexto); ?></p>
</section>

<ul class="canales">
    <li><a href="tel:08005552264"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><span>Llamanos gratis</span><strong>0800-555-2264</strong></a></li>
    <li><a href="mailto:contacto@banco.local"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><span>Escribinos un mail</span><strong>contacto@banco.local</strong></a></li>
    <li><a href="https://www.google.com/maps/search/?api=1&amp;query=Av.%20Corrientes%201234%2C%20CABA" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><span>Visitá la casa central</span><strong>Av. Corrientes 1234, CABA</strong></a></li>
</ul>

<div class="contacto-grilla">

    <section class="tarjeta">
        <h2>Dejanos tu mensaje</h2>
        <p class="texto-suave">Completá los datos y te contestamos al email que nos indiques.</p>
        <form action="procesos/procesar_contacto.php" method="POST" novalidate>
            <div class="campo">
                <label for="nombre">Nombre y apellido</label>
                <input type="text" id="nombre" name="nombre" autocomplete="name" maxlength="100" required value="<?php echo e(valorAnterior("nombre", $nombrePorDefecto)); ?>">
            </div>
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" spellcheck="false" maxlength="100" required value="<?php echo e(valorAnterior("email", $emailPorDefecto)); ?>">
            </div>
            <div class="campo">
                <label for="asunto">Asunto</label>
                <input type="text" id="asunto" name="asunto" maxlength="150" required value="<?php echo e(valorAnterior("asunto")); ?>">
            </div>
            <div class="campo">
                <label for="mensaje">Mensaje</label>
                <textarea id="mensaje" name="mensaje" maxlength="2000" required><?php echo e(valorAnterior("mensaje")); ?></textarea>
            </div>
            <button type="submit" class="boton">Enviar mensaje</button>
        </form>
    </section>

    <section class="tarjeta tarjeta-mapa">
        <div class="mapa">
            <iframe
                src="https://maps.google.com/maps?q=Av.%20Corrientes%201234%2C%20CABA%2C%20Argentina&z=16&output=embed"
                title="Mapa de la casa central de Future Bank"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
        </div>
        <div class="mapa-datos">
            <h2>Casa central</h2>
            <p>Av. Corrientes 1234, C1043 CABA</p>
            <p class="texto-suave">Lunes a viernes de 10 a 15 h. A dos cuadras del subte B, estación Uruguay.</p>
        </div>
    </section>

</div>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
