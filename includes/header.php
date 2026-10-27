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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo RAIZ; ?>css/estilos.css">
    <script src="<?php echo RAIZ; ?>js/validaciones.js" defer></script>
</head>
<body>

<header class="encabezado">
    <div class="contenedor encabezado-interior">
        <a href="<?php echo RAIZ; ?>index.php" class="logo">
            <span class="logo-icono" aria-hidden="true">BA</span>
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
                        <span title="<?php echo e(nombreRol(rolActual())); ?>"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg> <?php echo e($_SESSION["nombre"]); ?></span>
                        <a href="<?php echo RAIZ; ?>logout.php" class="boton boton-chico boton-claro">Salir</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo RAIZ; ?>contacto.php"<?php echo claseActivo("contacto.php"); ?>>Contacto</a></li>
                    <li><a href="<?php echo RAIZ; ?>registro.php"<?php echo claseActivo("registro.php"); ?>>Registrarse</a></li>
                    <li><a href="<?php echo RAIZ; ?>login.php" class="boton boton-chico boton-claro">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="contenedor contenido">
<?php mostrarMensajes(); ?>
