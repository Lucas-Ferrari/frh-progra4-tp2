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
    <title>Banco Andino - <?php echo e($titulo); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Geist+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo RAIZ; ?>img/favicon.png">
    <link rel="stylesheet" href="<?php echo RAIZ; ?>css/estilos.css">
    <script>
        // Antes de pintar: tema elegido y marca de que hay JavaScript (para las animaciones).
        document.documentElement.classList.add("js-anim");
        try { if (localStorage.getItem("tema") === "oscuro") document.documentElement.dataset.tema = "oscuro"; } catch (e) {}
    </script>
    <script src="<?php echo RAIZ; ?>js/validaciones.js" defer></script>
    <script src="<?php echo RAIZ; ?>js/animaciones.js" defer></script>
</head>
<body>

<header class="encabezado">
    <div class="contenedor encabezado-interior">
        <a href="<?php echo RAIZ; ?>index.php" class="logo">
            <img src="<?php echo RAIZ; ?>img/logo.png" alt="" width="46" height="46">
            <span class="logo-texto">Banco Andino</span>
        </a>

        <button type="button" class="menu-boton" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-principal">
            <span></span><span></span><span></span>
        </button>

        <nav id="menu-principal" class="menu">
            <ul>
                <li><a href="<?php echo RAIZ; ?>index.php"<?php echo claseActivo("index.php"); ?>>Inicio</a></li>

                <?php if (estaLogueado()): ?>
                    <li><a href="<?php echo RAIZ . panelSegunRol(rolActual()); ?>"<?php echo claseActivo("panel.php"); ?>>Mi panel</a></li>
                    <li><a href="<?php echo RAIZ; ?>contacto.php"<?php echo claseActivo("contacto.php"); ?>>Contacto</a></li>
                    <li><a href="<?php echo RAIZ; ?>cambiar_password.php"<?php echo claseActivo("cambiar_password.php"); ?>>Cambiar contraseña</a></li>
                    <li class="menu-usuario">
                        <span><?php echo e($_SESSION["nombre"]); ?><small><?php echo e(nombreRol(rolActual())); ?></small></span>
                        <a href="<?php echo RAIZ; ?>logout.php" class="boton boton-chico boton-claro">Salir</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo RAIZ; ?>contacto.php"<?php echo claseActivo("contacto.php"); ?>>Contacto</a></li>
                    <li><a href="<?php echo RAIZ; ?>registro.php"<?php echo claseActivo("registro.php"); ?>>Registrarse</a></li>
                    <li><a href="<?php echo RAIZ; ?>login.php" class="boton boton-chico boton-claro">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <button type="button" class="boton-tema" aria-label="Cambiar a modo oscuro">
            <svg class="luna-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/></svg>
            <svg class="sol-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>
    </div>
</header>

<main class="contenedor contenido">
<?php mostrarMensajes(); ?>
