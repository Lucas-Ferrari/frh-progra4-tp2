<?php

session_start();

require_once(__DIR__ . "/../includes/conexion.php");

if (!isset($_POST["email"]) || !isset($_POST["password"])) {
    die("Faltan datos del formulario.");
}

$email = $_POST["email"];
$password = $_POST["password"];

$tipoAcceso = isset($_POST["tipo_acceso"]) ? $_POST["tipo_acceso"] : "general";

$sql = "SELECT * FROM usuarios WHERE email='$email'";
$resultado = mysqli_query($conexion, $sql);

if (mysqli_num_rows($resultado) > 0) {

    $usuario = mysqli_fetch_assoc($resultado);

    if ($password === $usuario["password"]) {

        // Cada tipo de acceso ahora apunta a una LISTA de roles
        // permitidos, no a un único rol. "empleado" acepta tanto
        // Administrador (1) como Operador (2); "cliente" solo acepta
        // Cliente (3).
        $rolesPermitidos = [
            "empleado" => [1, 2],
            "cliente" => [3]
        ];

        if ($tipoAcceso !== "general" && isset($rolesPermitidos[$tipoAcceso])) {

            // in_array() busca si un valor existe DENTRO de un array.
            // Antes comparábamos con != contra un solo número;
            // ahora preguntamos "¿mi rol está en esta lista?".
            if (!in_array($usuario["id_rol"], $rolesPermitidos[$tipoAcceso])) {
                die("No tenés permisos de " . $tipoAcceso . " con esta cuenta.");
            }

        }

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["nombre"] = $usuario["nombre"];
        $_SESSION["id_rol"] = $usuario["id_rol"];

        if ($usuario["debe_cambiar_password"] == 1) {
            header("Location: ../cambiar_password.php");
            exit;
        }

        if ($usuario["id_rol"] == 1) {
            header("Location: ../admin/panel.php");
            exit;
        } elseif ($usuario["id_rol"] == 2) {
            header("Location: ../operador/panel.php");
            exit;
        } elseif ($usuario["id_rol"] == 3) {
            header("Location: ../cliente/panel.php");
            exit;
        } else {
            die("Rol de usuario no reconocido.");
        }

    } else {

        echo "Contraseña incorrecta";

    }

} else {

    echo "Usuario no encontrado";

}