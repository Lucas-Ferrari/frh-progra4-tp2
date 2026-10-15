<?php

require_once(__DIR__ . "/../includes/funciones.php");
require_once(__DIR__ . "/../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigir("contacto.php");
}

$nombre = campo($_POST, "nombre");
$email = campo($_POST, "email");
$asunto = campo($_POST, "asunto");
$mensaje = campo($_POST, "mensaje");

if ($nombre === "" || $email === "" || $asunto === "" || $mensaje === "") {
    volverConError("contacto.php", "campos");
}
if (!emailValido($email)) {
    volverConError("contacto.php", "email");
}

// Si la consulta la hace alguien logueado, la asociamos a su usuario.
$idUsuario = estaLogueado() ? (int) $_SESSION["id_usuario"] : null;

try {
    ejecutar(
        $conexion,
        "INSERT INTO contactos (nombre, email, asunto, mensaje, id_usuario) VALUES (?, ?, ?, ?, ?)",
        "ssssi",
        [$nombre, $email, $asunto, $mensaje, $idUsuario]
    );
} catch (mysqli_sql_exception $ex) {
    volverConError("contacto.php", "db");
}

// ---------- Aviso por mail a la dirección (simulada) del banco ----------
// Los saltos de línea se quitan de los datos que van en los encabezados
// para evitar "inyección de encabezados".
$emailSeguro = str_replace(["\r", "\n"], "", $email);
$asuntoSeguro = str_replace(["\r", "\n"], " ", $asunto);

$destinatario = "contacto@banco.local";
$asuntoMail = "=?UTF-8?B?" . base64_encode("[Consulta web] " . $asuntoSeguro) . "?=";
$cuerpo = "Nueva consulta desde el formulario de contacto\n\n" .
    "Nombre: " . $nombre . "\n" .
    "Email: " . $emailSeguro . "\n" .
    "Asunto: " . $asuntoSeguro . "\n\n" .
    "Mensaje:\n" . $mensaje . "\n";

$headers = "From: no-responder@banco.local\r\n" .
    "Reply-To: " . $emailSeguro . "\r\n" .
    "MIME-Version: 1.0\r\n" .
    "Content-Type: text/plain; charset=UTF-8\r\n";

// El @ evita el warning si el servidor no tiene configurado un servidor
// de correo: la consulta ya quedó guardada en la base igual.
$mailEnviado = @mail($destinatario, $asuntoMail, $cuerpo, $headers);

redirigir("contacto.php?ok=" . ($mailEnviado ? "contacto" : "contacto_sin_mail"));
