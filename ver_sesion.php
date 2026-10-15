<?php

session_start();

if (!isset($_SESSION["id_usuario"])) {
    die("No hay ninguna sesión activa. Iniciá sesión primero.");
}

echo "Estos son los datos que el servidor recuerda de vos:";
echo "<br>";
echo "ID de usuario: " . $_SESSION["id_usuario"];
echo "<br>";
echo "Nombre: " . $_SESSION["nombre"];
echo "<br>";
echo "Rol: " . $_SESSION["id_rol"];