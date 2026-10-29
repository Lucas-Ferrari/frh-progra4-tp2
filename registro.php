<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si ya está logueado, no tiene sentido registrarse de nuevo.
if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

$titulo = "Registrarse";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="acceso">
    <div class="acceso-tarjeta acceso-tarjeta-ancha">
        <img class="acceso-logo" src="img/FutureBank_icon_navbar.png" alt="" width="44" height="44">
        <h1>Creá tu cuenta</h1>
        <p class="acceso-subtitulo">Te abrimos una caja de ahorro en pesos sin costo.</p>

        <form action="procesos/procesar_registro.php" method="POST" novalidate>

            <div class="fila-campos">
                <div class="campo">
                    <label for="nombre" class="visualmente-oculto">Nombre</label>
                    <div class="entrada">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <input type="text" id="nombre" name="nombre" placeholder="Nombre" autocomplete="given-name" maxlength="50" required value="<?php echo e(valorAnterior("nombre")); ?>">
                    </div>
                </div>
                <div class="campo">
                    <label for="apellido" class="visualmente-oculto">Apellido</label>
                    <div class="entrada">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <input type="text" id="apellido" name="apellido" placeholder="Apellido" autocomplete="family-name" maxlength="50" required value="<?php echo e(valorAnterior("apellido")); ?>">
                    </div>
                </div>
            </div>

            <div class="fila-campos">
                <div class="campo">
                    <label for="dni" class="visualmente-oculto">DNI</label>
                    <div class="entrada">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="12" r="2.5"/><path d="M14 10h4M14 14h4"/></svg>
                        <input type="text" id="dni" name="dni" placeholder="DNI" inputmode="numeric" maxlength="8" required data-dni value="<?php echo e(valorAnterior("dni")); ?>">
                    </div>
                    <span class="ayuda">Sin puntos, 7 u 8 dígitos.</span>
                </div>
                <div class="campo">
                    <!-- La fecha no admite placeholder: el nombre va como prefijo dentro del campo -->
                    <label for="fecha_nacimiento" class="visualmente-oculto">Fecha de nacimiento</label>
                    <div class="entrada entrada-fecha">
                        <span class="entrada-prefijo" aria-hidden="true">Nacimiento</span>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date("Y-m-d"); ?>" required value="<?php echo e(valorAnterior("fecha_nacimiento")); ?>">
                    </div>
                </div>
            </div>

            <div class="campo">
                <label for="email" class="visualmente-oculto">Email</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input type="email" id="email" name="email" placeholder="Email" autocomplete="email" spellcheck="false" maxlength="100" required value="<?php echo e(valorAnterior("email")); ?>">
                </div>
            </div>

            <div class="fila-campos">
                <div class="campo">
                    <label for="password" class="visualmente-oculto">Contraseña</label>
                    <div class="entrada">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <input type="password" id="password" name="password" placeholder="Contraseña" autocomplete="new-password" required data-password-min="<?php echo PASSWORD_LONGITUD_MINIMA; ?>">
                        <button type="button" class="ver-password" data-ver-password="password" aria-label="Mostrar contraseña" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <span class="ayuda">Mínimo <?php echo PASSWORD_LONGITUD_MINIMA; ?> caracteres.</span>
                </div>
                <div class="campo">
                    <label for="password_repetir" class="visualmente-oculto">Repetir contraseña</label>
                    <div class="entrada">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <input type="password" id="password_repetir" name="password_repetir" placeholder="Repetir contraseña" autocomplete="new-password" required data-igual-a="password">
                    </div>
                </div>
            </div>

            <button type="submit" class="boton acceso-boton">
                Crear cuenta
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>

        <p class="acceso-pie">¿Ya tenés cuenta? <a href="login.php">Ingresá</a></p>
    </div>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
