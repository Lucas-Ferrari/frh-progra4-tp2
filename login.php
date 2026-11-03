<?php

require_once(__DIR__ . "/includes/funciones.php");

// Si ya hay sesión, va directo a su panel.
if (estaLogueado()) {
    redirigir(panelSegunRol(rolActual()));
}

// Tipo de acceso elegido: los clientes entran por "cliente" y los
// empleados (administradores y operadores) por "empleado".
$tipoAcceso = valorAnterior("tipo_acceso", isset($_GET["tipo"]) ? $_GET["tipo"] : "cliente");
if ($tipoAcceso !== "empleado") {
    $tipoAcceso = "cliente";
}

$titulo = "Ingresar";
require_once(__DIR__ . "/includes/header.php");

?>

<section class="acceso">
    <div class="acceso-tarjeta">
        <img class="acceso-logo" src="img/FutureBank_icon_navbar.png" alt="" width="44" height="44">
        <h1>Bienvenido de nuevo</h1>
        <p class="acceso-subtitulo">Ingresá para continuar a Future Bank</p>

        <form action="procesos/validar_login.php" method="POST" novalidate>

            <!-- fieldset + legend: agrupa los radios y los anuncia juntos en lectores de pantalla -->
            <fieldset class="campo acceso-tipo">
                <legend class="visualmente-oculto">Tipo de acceso</legend>
                <label>
                    <input type="radio" name="tipo_acceso" value="cliente" <?php echo $tipoAcceso === "cliente" ? "checked" : ""; ?>>
                    Clientes
                </label>
                <label>
                    <input type="radio" name="tipo_acceso" value="empleado" <?php echo $tipoAcceso === "empleado" ? "checked" : ""; ?>>
                    Empleados
                </label>
            </fieldset>

            <div class="campo">
                <label for="email" class="visualmente-oculto">Email</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    <input type="email" id="email" name="email" placeholder="Email" required autocomplete="username" value="<?php echo e(valorAnterior("email")); ?>">
                </div>
            </div>

            <div class="campo">
                <label for="password" class="visualmente-oculto">Contraseña</label>
                <div class="entrada">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                    <button type="button" class="ver-password" data-ver-password="password" aria-label="Mostrar contraseña" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="boton acceso-boton">
                Ingresar
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>

        <p class="acceso-pie">¿No tenés cuenta? <a href="registro.php">Registrate</a></p>
    </div>
</section>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
