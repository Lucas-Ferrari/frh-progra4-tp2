<?php

$servidor = "127.0.0.1:3306";
$usuario = "root";
$password = "root";
$baseDatos = "banco";

$conexion = mysqli_connect($servidor, $usuario, $password, 
                            $baseDatos);

if (!$conexion) {
    die("ERROR DE CONEXIÓN: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");