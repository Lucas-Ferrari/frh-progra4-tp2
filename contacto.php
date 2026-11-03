<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si hay sesión, pre-cargamos nombre y email del usuario.
$nombrePorDefecto = "";
$emailPorDefecto = "";

if (estaLogueado()) {
    $nombrePorDefecto = $_SESSION["nombre"] . " " . $_SESSION["apellido"];
    $emailPorDefecto = $_SESSION["email"];
}

$titulo = "Contacto";
require_once(__DIR__ . "/includes/header.php");

?>

<h1>Contacto</h1>

<div class="contacto-grilla">

    <section class="tarjeta">
        <h2>Visitanos</h2>
        <div class="mapa">
            <iframe
                src="https://maps.google.com/maps?q=Av.%20Corrientes%201234%2C%20CABA%2C%20Argentina&z=16&output=embed"
                title="Mapa de la sucursal de Future Bank"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
        </div>
        <p><a href="https://www.google.com/maps/search/?api=1&amp;query=Av.%20Corrientes%201234%2C%20CABA" target="_blank" rel="noopener">Ver en Google Maps ↗</a></p>
        <ul class="datos-contacto">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg> <strong>Casa central:</strong> Av. Corrientes 1234, C1043 CABA</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg> <strong>Teléfono:</strong> 0800-555-2264</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg> <strong>Email:</strong> contacto@banco.local</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> <strong>Horario de atención:</strong> lunes a viernes de 10 a 15 h</li>
        </ul>
    </section>

    <section class="tarjeta">
        <h2>Escribinos</h2>
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
            <button type="submit" class="boton">Enviar consulta</button>
        </form>
    </section>

</div>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
