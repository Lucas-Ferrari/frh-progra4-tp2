<?php

include("conexion.php");

// Sin WHERE: este SELECT puede traer 0, 1 o muchas filas.
$sql = "SELECT nombre, apellido, email, id_rol FROM usuarios";

$resultado = mysqli_query($conexion, $sql);

// mysqli_fetch_assoc() se llama DENTRO de la condición del while.
// En cada vuelta: agarra la próxima fila y la guarda en $usuario.
// Cuando ya no quedan filas, devuelve null -> el while se corta solo.
while ($usuario = mysqli_fetch_assoc($resultado)) {

    echo $usuario["nombre"] . " " . $usuario["apellido"];
    echo " - ";
    echo $usuario["email"];
    echo " (rol " . $usuario["id_rol"] . ")";
    echo "<br>";

}