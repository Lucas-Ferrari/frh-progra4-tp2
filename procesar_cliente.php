<?php


include("conexion.php");

// ... el resto del archivo sigue igual, sin tocar nada más
include("conexion.php");

function generarPasswordAleatoria($longitud = 8) {

    $pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $passwordGenerada = "";

    for ($i = 0; $i < $longitud; $i++) {
        $indiceAleatorio = rand(0, strlen($pool) - 1);
        $passwordGenerada .= $pool[$indiceAleatorio];
    }

    return $passwordGenerada;
}

if (!isset($_POST["nombre"]) || !isset($_POST["apellido"]) || !isset($_POST["dni"]) || !isset($_POST["fecha_nacimiento"]) || !isset($_POST["email"])) {
    die("Faltan datos del formulario.");
}

$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$dni = $_POST["dni"];
$fecha_nacimiento = $_POST["fecha_nacimiento"];
$email = $_POST["email"];

$passwordProvisoria = generarPasswordAleatoria(8);

$sql = "INSERT INTO usuarios (nombre, apellido, dni, fecha_nacimiento, email, password, fecha_registro, id_rol)
        VALUES ('$nombre', '$apellido', '$dni', '$fecha_nacimiento', '$email', '$passwordProvisoria', NOW(), 3)";

$resultado = mysqli_query($conexion, $sql);

if ($resultado) {

    echo "Cliente cargado correctamente.";
    echo "<br>";
    echo "Contraseña provisoria: " . $passwordProvisoria;
    echo "<br>";
    echo "Entregale esta contraseña al cliente. Deberá cambiarla en su primer ingreso.";

} else {

    echo "Error al cargar el cliente: " . mysqli_error($conexion);

}