<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$temporada = temporadaActiva($pdo);

// Rankings: victorias, podios, etc. (datos de la tabla de resultados)
$topVictorias = $pdo->query(
    "SELECT u.nombre_usuario, COUNT(*) AS victorias
     FROM resultados r JOIN usuarios u ON r.usuario = u.id
     WHERE r.posicion = 1
     GROUP BY u.id ORDER BY victorias DESC LIMIT 10"
)->fetchAll();

$topPodios = $pdo->query(
    "SELECT u.nombre_usuario, COUNT(*) AS podios
     FROM resultados r JOIN usuarios u ON r.usuario = u.id
     WHERE r.posicion BETWEEN 1 AND 3
     GROUP BY u.id ORDER BY podios DESC LIMIT 10"
)->fetchAll();

$titulo_pagina = 'Tops & Récords — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>⭐ Tops & Récords</h1>
    <p>Los mejores números de la competición</p>
</div>

<div class="container pb-40">
    <div class="grid-2">
        <div class="card card-accent">
            <h2 class="text-gold mb-2">🏆 Más Victorias</h2>
            <?php if (!empty($topVictorias)){
                 foreach ($topVictorias as $i => $v){ ?>
                    <div class="leader-row">
                        <span class="<?= clasePosicion($i+1) ?> fw-900 min-w-28"><?= $i+1 ?></span>
                        <span class="flex-1"><strong><?= e($v['nombre_usuario']) ?></strong></span>
                        <span class="text-gold fw-800"><?= $v['victorias'] ?></span>
                    </div>
                <?php }
                    } else{ ?>
                <p class="text-muted">Sin datos aún.</p>
            <?php } ?>
        </div>

        <div class="card card-accent">
            <h2 class="text-accent mb-2">🥇 Más Podios</h2>
            <?php if (!empty($topPodios)){
                 foreach ($topPodios as $i => $p){ ?>
                    <div class="leader-row">
                        <span class="<?= clasePosicion($i+1) ?> fw-900 min-w-28"><?= $i+1 ?></span>
                        <span class="flex-1"><strong><?= e($p['nombre_usuario']) ?></strong></span>
                        <span class="text-accent fw-800"><?= $p['podios'] ?></span>
                    </div>
                <?php }
                    } else{ ?>
                <p class="text-muted">Sin datos aún.</p>
            <?php } ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
