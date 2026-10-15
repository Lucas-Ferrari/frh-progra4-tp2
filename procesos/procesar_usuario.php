<?php

// ABM de usuarios (solo administradores).
// accion = alta | modificar | baja | reactivar | reset

require_once(__DIR__ . "/../includes/auth.php");
require_once(__DIR__ . "/../includes/conexion.php");

requerirPasswordActualizada();
requerirRol([ROL_ADMIN]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("admin/usuarios.php");
}

$accion = campo($_POST, "accion");
$idUsuario = (int) campo($_POST, "id_usuario");
$esYo = $idUsuario === (int) $_SESSION["id_usuario"];
$rolesValidos = [ROL_ADMIN, ROL_OPERADOR, ROL_CLIENTE];

// ---------------------------------------------------------------------
if ($accion === "alta" || $accion === "modificar") {
    $nombre = campo($_POST, "nombre");
    $apellido = campo($_POST, "apellido");
    $dni = campo($_POST, "dni");
    $fechaNacimiento = campo($_POST, "fecha_nacimiento");
    $email = strtolower(campo($_POST, "email"));
    // Si el admin se edita a sí mismo el select de rol viene deshabilitado:
    // conserva su rol de administrador.
    $idRol = $esYo ? ROL_ADMIN : (int) campo($_POST, "id_rol");

    $volver = $accion === "alta" ? "admin/usuario_form.php" : "admin/usuario_form.php?id=" . $idUsuario;

    $error = validarDatosPersonales($nombre, $apellido, $dni, $fechaNacimiento, $email);
    if ($error !== null) {
        volverConError($volver, $error);
    }
    if (!in_array($idRol, $rolesValidos, true)) {
        volverConError($volver, "rol");
    }
    if (usuarioDuplicado($conexion, $email, $dni, $accion === "modificar" ? $idUsuario : 0)) {
        volverConError($volver, "duplicado");
    }

    if ($accion === "alta") {
        $password = crearUsuarioConProvisoria($conexion, $nombre, $apellido, $dni, $fechaNacimiento, $email, $idRol);
        if ($password === null) {
            volverConError($volver, "db");
        }
        redirigir("admin/usuarios.php?ok=alta");
    }

    $existe = consultarUno($conexion, "SELECT id_usuario FROM usuarios WHERE id_usuario = ?", "i", [$idUsuario]);
    if ($existe === null) {
        redirigir("admin/usuarios.php?error=no_encontrado");
    }

    ejecutar(
        $conexion,
        "UPDATE usuarios SET nombre = ?, apellido = ?, dni = ?, fecha_nacimiento = ?, email = ?, id_rol = ? WHERE id_usuario = ?",
        "sssssii",
        [$nombre, $apellido, $dni, $fechaNacimiento, $email, $idRol, $idUsuario]
    );

    // Si se editó a sí mismo, actualizamos lo que se muestra en el menú.
    if ($esYo) {
        $_SESSION["nombre"] = $nombre;
        $_SESSION["apellido"] = $apellido;
        $_SESSION["email"] = $email;
    }

    redirigir("admin/usuarios.php?ok=modificacion");
}

// ---------------------------------------------------------------------
// Para el resto de las acciones el usuario tiene que existir.
$usuario = consultarUno($conexion, "SELECT id_usuario, email FROM usuarios WHERE id_usuario = ?", "i", [$idUsuario]);
if ($usuario === null) {
    redirigir("admin/usuarios.php?error=no_encontrado");
}

if ($accion === "baja") {
    // Un administrador no puede darse de baja a sí mismo.
    if ($esYo) {
        redirigir("admin/usuarios.php?error=auto_baja");
    }
    // Baja lógica: el usuario no se borra (tiene cuentas, consultas, etc.)
    ejecutar($conexion, "UPDATE usuarios SET activo = 0 WHERE id_usuario = ?", "i", [$idUsuario]);
    redirigir("admin/usuarios.php?ok=baja");
}

if ($accion === "reactivar") {
    ejecutar($conexion, "UPDATE usuarios SET activo = 1 WHERE id_usuario = ?", "i", [$idUsuario]);
    redirigir("admin/usuarios.php?ok=reactivado");
}

if ($accion === "reset") {
    $passwordProvisoria = generarPasswordProvisoria();
    $hash = password_hash($passwordProvisoria, PASSWORD_DEFAULT);

    ejecutar(
        $conexion,
        "UPDATE usuarios SET password = ?, debe_cambiar_password = 1 WHERE id_usuario = ?",
        "si",
        [$hash, $idUsuario]
    );

    if ($esYo) {
        $_SESSION["debe_cambiar_password"] = 1;
    }

    $_SESSION["flash_password"] = ["email" => $usuario["email"], "password" => $passwordProvisoria];
    redirigir("admin/usuarios.php?ok=reset");
}

redirigir("admin/usuarios.php");
