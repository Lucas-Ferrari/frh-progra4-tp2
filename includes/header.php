<?php

// Encabezado común: <head>, logo, menú de navegación y mensajes.
// Antes de incluirlo, cada página puede definir $titulo.

require_once(__DIR__ . "/funciones.php");

$titulo = isset($titulo) ? $titulo : "Inicio";
$paginaActual = basename($_SERVER["SCRIPT_NAME"]);

// Devuelve ' class="activo"' si el link apunta a la página actual.
function claseActivo($archivo)
{
    global $paginaActual;
    return $paginaActual === $archivo ? ' class="activo"' : "";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Color de la barra del navegador en el celular, según el tema del sistema -->
    <meta name="theme-color" content="#f5f5f8" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b0a10" media="(prefers-color-scheme: dark)">
    <title>Future Bank - <?php echo e($titulo); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo RAIZ; ?>img/FutureBank_favicon.png">
    <script>
        // Va antes del CSS para que no haya un parpadeo de color: aplica el tema
        // que la persona eligió con el botón. Si nunca eligió, manda el sistema.
        // La clase "js" habilita la barra transparente: sin JS la barra nunca
        // pasaría a sólida al bajar, así que en ese caso queda sólida siempre.
        document.documentElement.classList.add("js");
        try {
            var temaGuardado = localStorage.getItem("tema");
            if (temaGuardado === "oscuro" || temaGuardado === "claro") {
                document.documentElement.dataset.tema = temaGuardado;
            }
        } catch (e) {}
    </script>
    <?php
    // Cache busting: el ?v= cambia cada vez que se modifica el archivo
    // (filemtime = fecha de última modificación), así el navegador
    // descarga la versión nueva en lugar de usar la que tiene en caché.
    $versionCss = filemtime(__DIR__ . "/../css/estilos.css");
    $versionJs = filemtime(__DIR__ . "/../js/validaciones.js");
    $versionAnimaciones = filemtime(__DIR__ . "/../js/animaciones.js");
    ?>
    <link rel="stylesheet" href="<?php echo RAIZ; ?>css/estilos.css?v=<?php echo $versionCss; ?>">
    <script src="<?php echo RAIZ; ?>js/validaciones.js?v=<?php echo $versionJs; ?>" defer></script>
    <!-- Animaciones separadas de las validaciones: cada archivo, una responsabilidad -->
    <script src="<?php echo RAIZ; ?>js/animaciones.js?v=<?php echo $versionAnimaciones; ?>" defer></script>
</head>
<body>

<!-- Permite saltear el menú con teclado o lector de pantalla e ir directo al contenido -->
<a href="#contenido" class="saltar">Saltar al contenido</a>

<header class="encabezado">
    <div class="contenedor encabezado-interior">
        <a href="<?php echo RAIZ; ?>index.php" class="logo">
            <img class="logo-icono" src="<?php echo RAIZ; ?>img/FutureBank_icon_navbar.png" alt="" width="36" height="36">
            <span class="logo-texto">Future Bank</span>
        </a>

        <button type="button" class="menu-boton" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-principal">
            <span></span><span></span><span></span>
        </button>

        <nav id="menu-principal" class="menu">
            <ul>
                <li><a href="<?php echo RAIZ; ?>index.php"<?php echo claseActivo("index.php"); ?>>Inicio</a></li>

                <?php if (estaLogueado()): ?>
                    <li><a href="<?php echo RAIZ . panelSegunRol(rolActual()); ?>"<?php echo claseActivo("panel.php"); ?>>Mi panel</a></li>
                    <li><a href="<?php echo RAIZ; ?>nosotros.php"<?php echo claseActivo("nosotros.php"); ?>>Nosotros</a></li>
                    <li><a href="<?php echo RAIZ; ?>contacto.php"<?php echo claseActivo("contacto.php"); ?>>Contacto</a></li>
                    <li><a href="<?php echo RAIZ; ?>cambiar_password.php"<?php echo claseActivo("cambiar_password.php"); ?>>Cambiar contraseña</a></li>
                    <li class="menu-usuario">
                        <!-- Nombre y, debajo, el rol (visible, no solo en un title) -->
                        <span class="usuario"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg> <span><?php echo e($_SESSION["nombre"]); ?><small><?php echo e(nombreRol(rolActual())); ?></small></span></span>
                        <a href="<?php echo RAIZ; ?>logout.php" class="boton boton-chico boton-claro">Salir</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo RAIZ; ?>nosotros.php"<?php echo claseActivo("nosotros.php"); ?>>Nosotros</a></li>
                    <li><a href="<?php echo RAIZ; ?>contacto.php"<?php echo claseActivo("contacto.php"); ?>>Contacto</a></li>
                    <li><a href="<?php echo RAIZ; ?>login.php" class="boton boton-chico boton-claro">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Cambio manual de tema. animaciones.js actualiza el aria-label
             según el tema activo; los dos íconos se alternan por CSS. -->
        <button type="button" class="boton-tema" aria-label="Cambiar a modo oscuro">
            <svg class="luna-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/></svg>
            <svg class="sol-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
    </div>
</header>

<?php // Los toasts van fuera de <main>: adentro serían el primer hijo y romperían las reglas :first-child del layout
mostrarMensajes(); ?>

<main id="contenido" class="contenedor contenido" tabindex="-1">
<?php mostrarPasswordProvisoria(); ?>
