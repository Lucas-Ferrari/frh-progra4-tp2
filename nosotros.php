<?php

// Integrantes del equipo. Cargos ficticios, frases 100% reales.
$integrantes = [
    [
        "nombre" => "Lucas Ferrari",
        "rol" => "Chief Technology Officer",
        "foto" => "LucasFerrari.jpeg",
        "bio" => "Define la arquitectura de Future Bank y decide qué se despliega un viernes a las 18 hs (spoiler: todo).",
        "stack" => ["PHP", "MySQL", "Railway", "Git"],
        "dato" => ["valor" => "1.337", "etiqueta" => "commits con mensaje \"fix\""],
        "frase" => "En mi máquina funciona. Por eso la mandamos a producción.",
    ],
    [
        "nombre" => "Alan Infante",
        "rol" => "Lead Backend Engineer",
        "foto" => "AlanInfante.jpeg",
        "bio" => "Hace que cada transferencia llegue a destino y que ningún centavo se pierda entre dos sentencias SQL.",
        "stack" => ["PHP", "PDO", "APIs REST", "Linux"],
        "dato" => ["valor" => "0", "etiqueta" => "bugs admitidos públicamente"],
        "frase" => "No es un bug, es una feature que todavía no documentamos.",
    ],
    [
        "nombre" => "Ignacio Ruiz Diaz",
        "rol" => "Arquitecto de Bases de Datos",
        "foto" => "NachoRuizDiaz.jpeg",
        "bio" => "Diseñó el modelo de cuentas y movimientos. Normaliza tablas por placer y sueña en diagramas entidad-relación.",
        "stack" => ["MySQL", "SQL", "Índices", "Backups"],
        "dato" => ["valor" => "3FN", "etiqueta" => "o nada, sin negociar"],
        "frase" => "Tengo un chiste sobre SQL, pero primero necesito hacer un JOIN.",
    ],
    [
        "nombre" => "Tobias Acosta",
        "rol" => "Frontend & UX Engineer",
        "foto" => "TobiasAcosta.jpeg",
        "bio" => "Responsable de que el banco se vea del futuro y funcione en el celular de tu abuela, en modo claro y oscuro.",
        "stack" => ["HTML", "CSS", "JavaScript", "Figma"],
        "dato" => ["valor" => "42", "etiqueta" => "tonos de violeta probados"],
        "frase" => "Centrar un div es fácil. Lo difícil es centrar mi vida.",
    ],
];

$titulo = "Sobre nosotros";
require_once(__DIR__ . "/includes/header.php");

?>

<div class="fondo-sucursal" aria-hidden="true"></div>

<div class="hero-equipo">
    <h1>Sobre nosotros</h1>
    <p class="intro-equipo">El equipo que construye Future Bank, una línea de código (y un café) a la vez.</p>
</div>

<div class="grilla grilla-equipo">
    <?php foreach ($integrantes as $persona): ?>
        <section class="tarjeta integrante">
            <div class="integrante-cabecera">
                <div class="integrante-foto">
                    <img src="img/nosotros/<?php echo e($persona["foto"]); ?>" alt="Foto de <?php echo e($persona["nombre"]); ?>" loading="lazy">
                </div>
                <div>
                    <h2><?php echo e($persona["nombre"]); ?></h2>
                    <p class="integrante-rol"><?php echo e($persona["rol"]); ?></p>
                </div>
            </div>

            <p class="integrante-bio"><?php echo e($persona["bio"]); ?></p>

            <ul class="integrante-stack" aria-label="Tecnologías">
                <?php foreach ($persona["stack"] as $tecnologia): ?>
                    <li><?php echo e($tecnologia); ?></li>
                <?php endforeach; ?>
            </ul>

            <p class="integrante-dato">
                <strong><?php echo e($persona["dato"]["valor"]); ?></strong>
                <span><?php echo e($persona["dato"]["etiqueta"]); ?></span>
            </p>

            <p class="integrante-frase"><?php echo e($persona["frase"]); ?></p>
        </section>
    <?php endforeach; ?>
</div>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
