<?php

// Gestión de consultas de contacto (solo administradores).
// accion = leido | eliminar

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("admin/contactos.php");
}

$accion = campo($_POST, "accion");
$idContacto = (int) campo($_POST, "id_contacto");

if ($accion === "leido") {
    ejecutar($conexion, "UPDATE contactos SET leido = 1 WHERE id_contacto = ?", "i", [$idContacto]);
    redirigir("admin/contactos.php?ok=leido");
}

if ($accion === "eliminar") {
    $afectadas = ejecutar($conexion, "DELETE FROM contactos WHERE id_contacto = ?", "i", [$idContacto]);
    redirigir("admin/contactos.php?" . ($afectadas > 0 ? "ok=baja" : "error=no_encontrado"));
}

redirigir("admin/contactos.php");
