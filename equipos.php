<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$equipos = $pdo->query(
    "SELECT e.*,
            (SELECT COUNT(*) FROM usuarios u WHERE u.equipo = e.id AND u.rango_piloto != 'admin') AS num_pilotos
     FROM equipos e ORDER BY e.nombre ASC"
)->fetchAll();

// Agrupamos los pilotos por equipo para poder pintarlos debajo de cada escudería
$pilotosPorEquipo = [];
$stmtPilotos = $pdo->query("SELECT id, nombre_usuario, equipo, rango_piloto FROM usuarios WHERE equipo IS NOT NULL AND rango_piloto IN ('piloto','jefe_equipo') ORDER BY CASE WHEN rango_piloto = 'jefe_equipo' THEN 0 ELSE 1 END, nombre_usuario ASC");
foreach ($stmtPilotos as $p) {
    $pilotosPorEquipo[$p['equipo']][] = $p;
}

$usuario = null;
if (estaLogueado()) {
    $usuario = usuarioActual($pdo);
}

$titulo_pagina = 'Equipos — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-40">
    <div class="text-center mb-3">
        <h1>🛡️ Equipos</h1>
        <p class="text-muted">Escuderías que compiten en el campeonato</p>
        <span class="badge badge-success mt-1"><?= count($equipos) ?> equipos</span>
    </div>

    <?php if (esAdmin()) { ?>
        <div class="text-center mb-3">
            <a href="<?= SITE_URL ?>admin/equipos.php" class="btn btn-admin">⚙️ Gestionar equipos</a>
        </div>
    <?php } ?>

    <div class="grid-2">
        <?php foreach ($equipos as $eq) { ?>
            <?php $esMiEquipo = $usuario && $usuario['equipo'] == $eq['id']; ?>
            <?php
            $claseTarjeta = 'card card-accent';
            if ($esMiEquipo) {
                $claseTarjeta .= ' card-highlight';
            }
            ?>
            <div class="<?= $claseTarjeta ?>">
                <div class="flex items-center gap-15 mb-20">
                    <div class="icon-team">🛡️</div>
                    <div>
                        <h3 class="m-0"><?= e($eq['nombre']) ?></h3>
                        <div class="text-muted fs-12">
                            <?= $eq['num_pilotos'] ?> pilotos inscritos
                        </div>
                    </div>
                    <?php if ($esMiEquipo) { ?>
                        <span class="badge badge-accent ml-auto text-center">Tu equipo</span>
                    <?php } ?>
                </div>

                <div class="card-divider">
                    <?php
                    $pilotos = [];
                    if (isset($pilotosPorEquipo[$eq['id']])) {
                        $pilotos = $pilotosPorEquipo[$eq['id']];
                    }
                    ?>
                    <?php if (!empty($pilotos)) { ?>
                        <div class="fs-11 fw-700 uppercase text-muted mb-10 ls-1">Alineación</div>
                        <div class="flex flex-col gap-1">
                            <?php foreach ($pilotos as $p) { ?>
                                <div class="team-member-row">
                                    <span><?= e($p['nombre_usuario']) ?></span>
                                    <?php
                                    $badgeClass = 'badge-accent';
                                    $badgeLabel = 'Piloto';
                                    switch ($p['rango_piloto']) {
                                        case 'jefe_equipo':
                                            $badgeClass = 'badge-success';
                                            $badgeLabel = 'Jefe de equipo';
                                            break;
                                        default:
                                            $badgeClass = 'badge-accent';
                                            $badgeLabel = 'Piloto';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?> badge-xs"><?= $badgeLabel ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted fs-13 text-center p-10">Sin pilotos asignados</p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if (empty($equipos)) { ?>
        <div class="text-center text-muted p-40">No hay equipos registrados aún.</div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
