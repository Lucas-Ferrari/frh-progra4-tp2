<?php

require_once(__DIR__ . "/includes/funciones.php");

if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

// "Empezar de nuevo" descarta el pedido en curso.
if (isset($_GET["reiniciar"])) {
    unset($_SESSION["recuperacion"]);
}

$rec = isset($_SESSION["recuperacion"]) && time() <= $_SESSION["recuperacion"]["expira"] ? $_SESSION["recuperacion"] : null;
$flash = isset($_SESSION["flash_codigo"]) ? $_SESSION["flash_codigo"] : null;
unset($_SESSION["flash_codigo"]);

$titulo = "Recuperar contraseña";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="acceso">
    <div class="acceso-tarjeta">
        <img class="acceso-logo" src="img/FutureBank_icon_navbar.png" alt="" width="44" height="44">
        <h1>Recuperar contraseña</h1>

        <?php if ($flash !== null) : ?>
            <?php if (!$flash["registrado"]) : ?>
                <script>console.log("Debug: el email no está registrado o está inactivo, ningún código va a funcionar.");</script>
            <?php else : ?>
                <script>console.log("Código de recuperación: " + <?php echo json_encode($flash["codigo"]); ?>);</script>
            <?php endif; ?>
            <?php if ($flash["registrado"] && !$flash["mail_enviado"]) : ?>
                <div class="mensaje mensaje-info">No se pudo enviar el mail desde este servidor. Código (debug): <code class="password-provisoria"><?php echo e($flash["codigo"]); ?></code></div>
            <?php endif; ?>
        <?php endif; ?>

    <?php if ($rec === null) : ?>
        <p class="acceso-subtitulo">Ingresá tu email y te enviamos un código para crear una contraseña nueva.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="email">
            <div class="campo">
                <label for="email" class="visualmente-oculto">Email</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input type="email" id="email" name="email" placeholder="Email" autocomplete="email" spellcheck="false" maxlength="100" required value="<?php echo e(valorAnterior("email")); ?>">
                </div>
            </div>
            <button type="submit" class="boton acceso-boton">Enviar código <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </form>

    <?php elseif (!$rec["verificado"]) : ?>
        <p class="acceso-subtitulo">Si <strong><?php echo e($rec["email"]); ?></strong> está registrado, te enviamos un código de 6 dígitos.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="codigo">
            <div class="campo">
                <label for="codigo" class="visualmente-oculto">Código</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input type="text" id="codigo" name="codigo" placeholder="Código de 6 dígitos" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>
                </div>
            </div>
            <button type="submit" class="boton acceso-boton">Validar código <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </form>

    <?php else : ?>
        <p class="acceso-subtitulo">Código validado. Elegí tu contraseña nueva.</p>
        <form action="procesos/procesar_recuperar_password.php" method="POST" novalidate>
            <input type="hidden" name="paso" value="password">
            <div class="campo">
                <label for="password" class="visualmente-oculto">Contraseña nueva</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="Contraseña nueva" autocomplete="new-password" required data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
                    <button type="button" class="ver-password" data-ver-password="password" aria-label="Mostrar contraseña" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
                <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
            </div>
            <div class="campo">
                <label for="password_repetir" class="visualmente-oculto">Repetir contraseña</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input type="password" id="password_repetir" name="password_repetir" placeholder="Repetir contraseña" autocomplete="new-password" required data-igual-a="password">
                    <button type="button" class="ver-password" data-ver-password="password_repetir" aria-label="Mostrar contraseña" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
            </div>
            <button type="submit" class="boton acceso-boton">Guardar contraseña <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </form>
    <?php endif; ?>

        <p class="acceso-pie"><a href="login.php">Volver a ingresar</a><?php if ($rec !== null) : ?> · <a href="recuperar_password.php?reiniciar=1">Empezar de nuevo</a><?php endif; ?></p>
    </div>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
