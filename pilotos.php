<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pilotos = $pdo->query(
    "SELECT u.*, e.nombre AS equipo_nombre
     FROM usuarios u
     LEFT JOIN equipos e ON u.equipo = e.id
     WHERE u.rango_piloto IN ('piloto','jefe_equipo')
     ORDER BY u.nombre_usuario ASC"
)->fetchAll();

$titulo_pagina = 'Pilotos — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-40">
    <div class="text-center mb-3">
        <h1>🏎️ Pilotos</h1>
        <p class="text-muted">Directorio de pilotos inscritos en la competición</p>
        <div class="mt-1">
            <span class="badge badge-accent"><?= count($pilotos) ?> pilotos registrados</span>
        </div>
    </div>

    <?php if (esAdmin()) { ?>
        <div class="text-center mb-3">
            <a href="<?= SITE_URL ?>admin/pilotos.php" class="btn btn-admin">⚙️ Gestionar pilotos</a>
        </div>
    <?php } ?>

    <div class="grid-3">
        <?php foreach ($pilotos as $p) { ?>
            <?php $esYo = estaLogueado() && $_SESSION['usuario_id'] == $p['id']; ?>
            <?php
            if (!isset($p['rango_piloto']) || $p['rango_piloto'] === '') {
                $rango = 'piloto';
            } else {
                $rango = $p['rango_piloto'];
            }

            $claseCard = 'card text-center';
            if ($esYo) {
                $claseCard .= ' row-highlight';
            }

            $equipoNombre = 'Sin Equipo';
            if (isset($p['equipo_nombre']) && $p['equipo_nombre'] !== '') {
                $equipoNombre = $p['equipo_nombre'];
            }

            $rangoBadgeClass = 'badge-accent';
            $rangoLabel = 'Piloto';
            switch ($rango) {
                case 'jefe_equipo':
                    $rangoBadgeClass = 'badge-success';
                    $rangoLabel = 'Jefe de equipo';
                    break;
                default:
                    $rangoBadgeClass = 'badge-accent';
                    $rangoLabel = 'Piloto';
                    break;
            }
            ?>
            <div class="<?= $claseCard ?>">
                <div class="empty-icon mb-15">🏎️</div>
                <h3 class="mb-4"><?= e($p['nombre_usuario']) ?></h3>
                <div class="mb-10 flex flex-wrap gap-10 justify-center">
                    <span class="badge <?= $rangoBadgeClass ?> fs-10"><?= e($rangoLabel) ?></span>
                    <?php if (!empty($p['etiquetas'])) { ?>
                        <?php foreach(explode(',', $p['etiquetas']) as $tag) { ?>
                            <span class="badge badge-soft"><?= e(trim($tag)) ?></span>
                        <?php } ?>
                    <?php } ?>
                </div>
                <p class="text-success pilotos-equipo fw-700 mb-15"><?= e($equipoNombre) ?></p>

                <div class="text-muted fs-12 mt-10">
                    Inscrito el: <?= date('d/m/Y', strtotime($p['fecha_registro'])) ?>
                </div>

                <?php if ($esYo) { ?>
                    <div class="mt-15">
                        <span class="badge badge-accent">Tú</span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <?php if (empty($pilotos)) { ?>
        <div class="text-center text-muted p-40">No hay pilotos registrados aún.</div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
