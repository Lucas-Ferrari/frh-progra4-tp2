<?php

// Integrantes del equipo. Mientras esté vacío se muestran tarjetas de
// esqueleto. Cada uno: ["nombre" => "", "rol" => "", "descripcion" => ""].
$integrantes = [];

$titulo = "Sobre nosotros";
require_once(__DIR__ . "/includes/header.php");

?>

<h1>Sobre nosotros</h1>

<?php if (empty($integrantes)): ?>
    <p class="mensaje mensaje-info">Sección en construcción: pronto vas a conocer al equipo detrás de Banco Andino.</p>

    <div class="grilla" aria-hidden="true">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="tarjeta integrante">
                <div class="esqueleto esqueleto-avatar"></div>
                <div class="esqueleto esqueleto-linea" style="width: 60%"></div>
                <div class="esqueleto esqueleto-linea" style="width: 40%"></div>
                <div class="esqueleto esqueleto-linea"></div>
                <div class="esqueleto esqueleto-linea" style="width: 80%"></div>
            </div>
        <?php endfor; ?>
    </div>
<?php else: ?>
    <div class="grilla">
        <?php foreach ($integrantes as $persona): ?>
            <section class="tarjeta integrante">
                <div class="integrante-avatar" aria-hidden="true"><?php echo e(mb_substr($persona["nombre"], 0, 1)); ?></div>
                <h2><?php echo e($persona["nombre"]); ?></h2>
                <p class="integrante-rol"><?php echo e($persona["rol"]); ?></p>
                <p><?php echo e($persona["descripcion"]); ?></p>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once(__DIR__ . "/includes/footer.php"); ?>
