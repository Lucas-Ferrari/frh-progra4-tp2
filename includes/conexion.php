<?php

// Único punto de conexión a la base de datos.
// Se incluye siempre con require_once(__DIR__ . "/../includes/conexion.php")
// (o la ruta que corresponda) para no abrir dos conexiones.

$servidor = "127.0.0.1:3306";
$usuario = "root";
$password = "root";   // En XAMPP por defecto es "" (vacío)
$baseDatos = "banco";

// Que los errores de MySQL se lancen como excepciones: así podemos
// hacer rollback de las transacciones con try/catch.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = mysqli_connect($servidor, $usuario, $password, $baseDatos);
} catch (mysqli_sql_exception $ex) {
    die("ERROR DE CONEXIÓN: " . $ex->getMessage());
}

mysqli_set_charset($conexion, "utf8mb4");
