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
                title="Mapa de la sucursal de Banco Andino"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen></iframe>
        </div>
        <p><a href="https://www.google.com/maps/search/?api=1&amp;query=Av.%20Corrientes%201234%2C%20CABA" target="_blank" rel="noopener">Ver en Google Maps ↗</a></p>
        <ul class="datos-contacto">
            <li><span aria-hidden="true">📍</span> <strong>Casa central:</strong> Av. Corrientes 1234, C1043 CABA</li>
            <li><span aria-hidden="true">📞</span> <strong>Teléfono:</strong> 0800-555-2264</li>
            <li><span aria-hidden="true">✉️</span> <strong>Email:</strong> contacto@banco.local</li>
            <li><span aria-hidden="true">🕙</span> <strong>Horario de atención:</strong> lunes a viernes de 10 a 15 h</li>
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
