<?php

// Funciones de control de acceso. Toda página interna debe llamar,
// en este orden, a requerirLogin(), requerirPasswordActualizada()
// y (si corresponde) requerirRol([...]).

require_once(__DIR__ . "/funciones.php");

// Si no hay sesión (o expiró por inactividad), vuelve al login.
function requerirLogin()
{
    if (!estaLogueado()) {
        redirigir("login.php?error=sesion");
    }

    $ultimoAcceso = isset($_SESSION["ultimo_acceso"]) ? $_SESSION["ultimo_acceso"] : time();

    if (time() - $ultimoAcceso > SESION_MAX_INACTIVIDAD) {
        session_unset();
        session_destroy();
        redirigir("login.php?error=sesion");
    }

    $_SESSION["ultimo_acceso"] = time();
}

// Si el rol del usuario logueado no está en la lista, lo devuelve a su
// propio panel con un mensaje de error.
function requerirRol(array $roles)
{
    requerirLogin();

    if (!in_array(rolActual(), $roles, true)) {
        redirigir(panelSegunRol(rolActual()) . "?error=permisos");
    }
}

// Si el usuario tiene una contraseña provisoria, no puede usar el sitio
// hasta cambiarla.
function requerirPasswordActualizada()
{
    requerirLogin();

    if (!empty($_SESSION["debe_cambiar_password"])) {
        redirigir("cambiar_password.php");
    }
}
