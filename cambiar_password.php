<?php

require_once(__DIR__ . "/includes/auth.php");

// Solo pedimos login: esta es justamente la página a la que se
// redirige cuando la contraseña está pendiente de cambio.
requerirLogin();

$obligatorio = !empty($_SESSION["debe_cambiar_password"]);

$titulo = "Cambiar contraseña";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="acceso">
<div class="acceso-tarjeta">
    <img class="acceso-logo" src="img/FutureBank_icon_navbar.png" alt="" width="44" height="44">
    <h1>Cambiar contraseña</h1>
    <p class="acceso-subtitulo">Elegí una contraseña nueva para tu cuenta</p>

    <?php if ($obligatorio): ?>
        <div class="mensaje mensaje-info">
            Hola, <?php echo e($_SESSION["nombre"]); ?>. Estás usando una contraseña provisoria:
            tenés que elegir una nueva para continuar.
        </div>
    <?php endif; ?>

    <form action="procesos/procesar_cambio_password.php" method="POST" novalidate>
        <div class="campo">
            <label for="password_actual" class="visualmente-oculto"><?php echo $obligatorio ? "Contraseña provisoria" : "Contraseña actual"; ?></label>
            <div class="entrada">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input type="password" id="password_actual" name="password_actual" placeholder="<?php echo $obligatorio ? "Contraseña provisoria" : "Contraseña actual"; ?>" required autocomplete="current-password">
                <button type="button" class="ver-password" data-ver-password="password_actual" aria-label="Mostrar contraseña" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
        <div class="campo">
            <label for="password_nueva" class="visualmente-oculto">Contraseña nueva</label>
            <div class="entrada">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input type="password" id="password_nueva" name="password_nueva" placeholder="Contraseña nueva" required autocomplete="new-password" data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
                <button type="button" class="ver-password" data-ver-password="password_nueva" aria-label="Mostrar contraseña" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
        </div>
        <div class="campo">
            <label for="password_nueva_repetir" class="visualmente-oculto">Repetir contraseña nueva</label>
            <div class="entrada">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <input type="password" id="password_nueva_repetir" name="password_nueva_repetir" placeholder="Repetir contraseña nueva" required autocomplete="new-password" data-igual-a="password_nueva">
                <button type="button" class="ver-password" data-ver-password="password_nueva_repetir" aria-label="Mostrar contraseña" aria-pressed="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>
        <button type="submit" class="boton acceso-boton">
            Cambiar contraseña
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>
</div>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
