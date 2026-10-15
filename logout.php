<?php

require_once(__DIR__ . "/includes/funciones.php");

// Vaciamos las variables de sesión y destruimos la sesión en el servidor.
session_unset();
session_destroy();

redirigir("index.php?ok=logout");
