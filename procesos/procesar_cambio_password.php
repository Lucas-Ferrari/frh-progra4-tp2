<?php

// Necesitamos saber quién sos (id_usuario) para saber qué fila actualizar.
session_start();

if (!isset($_SESSION["id_usuario"])) {
    die("No hay ninguna sesión activa. Iniciá sesión primero.");
}

require_once(__DIR__ . "/../includes/conexion.php");

// Guard clause: confirmamos que llegaron los 3 campos del formulario.
if (!isset($_POST["password_actual"]) || !isset($_POST["password_nueva"]) || !isset($_POST["password_nueva_repetir"])) {
    die("Faltan datos del formulario.");
}

$password_actual = $_POST["password_actual"];
$password_nueva = $_POST["password_nueva"];
$password_nueva_repetir = $_POST["password_nueva_repetir"];

// El id_usuario sale de la SESIÓN, no del formulario.
// Así evitamos que alguien intente cambiar la contraseña de otro usuario
// mandando un id distinto a mano.
$id_usuario = $_SESSION["id_usuario"];

// Verificamos primero que las dos contraseñas nuevas coincidan,
// antes de tocar la base de datos.
if ($password_nueva !== $password_nueva_repetir) {
    die("La contraseña nueva y su repetición no coinciden.");
}

// Buscamos la contraseña ACTUAL guardada, para compararla con la que
// escribió el usuario (no confiamos en que "ya está logueado" sea
// suficiente para cambiar la contraseña sin volver a confirmarla).
$sql = "SELECT password FROM usuarios WHERE id_usuario = $id_usuario";
$resultado = mysqli_query($conexion, $sql);
$usuario = mysqli_fetch_assoc($resultado);

if ($password_actual !== $usuario["password"]) {
    die("La contraseña actual no es correcta.");
}

// Todo validado: actualizamos la contraseña y bajamos la bandera
// debe_cambiar_password a 0 (ya no está pendiente el cambio obligatorio).
// WHERE id_usuario = $id_usuario es CRÍTICO: sin esto, se actualizarían
// TODAS las filas de la tabla.
$sqlUpdate = "UPDATE usuarios 
              SET password = '$password_nueva', debe_cambiar_password = 0 
              WHERE id_usuario = $id_usuario";

$resultadoUpdate = mysqli_query($conexion, $sqlUpdate);

if ($resultadoUpdate) {
    echo "Contraseña actualizada correctamente.";
} else {
    echo "Error al actualizar la contraseña: " . mysqli_error($conexion);
}