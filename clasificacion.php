<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$temporada = temporadaActiva($pdo);
$tabActiva = 'pilotos';
if (isset($_GET['tab']) && $_GET['tab'] !== '') {
    $tabActiva = $_GET['tab'];
}
if ($tabActiva !== 'pilotos' && $tabActiva !== 'equipos') {
    $tabActiva = 'pilotos';
}

$cPilotos = [];
$cEquipos = [];
if ($temporada) {
    $cPilotos = clasificacionPilotos($pdo, $temporada['id']);
    $cEquipos = clasificacionEquipos($pdo, $temporada['id']);
}

$clase_tab_pilotos = 'tab-btn';
$clase_tab_equipos = 'tab-btn';
$clase_contenido_pilotos = 'tab-content';
$clase_contenido_equipos = 'tab-content';

switch ($tabActiva) {
    case 'equipos':
        $clase_tab_equipos .= ' active';
        $clase_contenido_equipos .= ' active';
        break;
    default:
        $clase_tab_pilotos .= ' active';
        $clase_contenido_pilotos .= ' active';
        break;
}

$titulo_pagina = 'Clasificación — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-40">
    <div class="text-center mb-3">
        <h1>📊 Clasificación General</h1>
        <?php if ($temporada) { ?>
            <p class="text-muted"><?= e($temporada['nombre']) ?></p>
        <?php } else { ?>
            <p class="text-muted">No hay temporada activa</p>
        <?php } ?>
    </div>

    <!-- Pestañas para cambiar entre pilotos y equipos -->
    <div class="tabs justify-center">
        <button class="<?= $clase_tab_pilotos ?>" data-tab="pilotos" onclick="showTab('pilotos')">🏎️ Pilotos</button>
        <button class="<?= $clase_tab_equipos ?>" data-tab="equipos" onclick="showTab('equipos')">🛡️ Equipos</button>
    </div>

    <!-- Contenido: tabla de pilotos -->
    <div class="<?= $clase_contenido_pilotos ?>" id="tab-pilotos">
        <?php if (!empty($cPilotos)) { ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Piloto</th>
                            <th>Equipo</th>
                            <th class="text-right">Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cPilotos as $i => $p) { ?>
                            <?php
                            $pos = $i + 1;
                            $esYo = estaLogueado() && $_SESSION['usuario_id'] == $p['id'];
                            $claseFila = '';
                            if ($esYo) {
                                $claseFila = 'row-highlight';
                            }
                            ?>
                            <tr class="<?= $claseFila ?>">
                                <td class="<?= clasePosicion($pos) ?> fw-900">
                                    <?= medallaPosicion($pos) ?> <?= $pos ?>
                                </td>
                                <td>
                                    <strong><?= e($p['nombre_usuario']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($p['equipo']) { ?>
                                        <span class="text-accent"><?= e($p['equipo']) ?></span>
                                    <?php } else { ?>
                                        <span class="text-muted">—</span>
                                    <?php } ?>
                                </td>
                                <td class="points-cell">
                                    <?= $p['total_puntos'] ?> <span class="text-muted points-unit">pts</span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-12">
                <a href="<?= SITE_URL ?>tops.php" class="btn btn-accent btn-sm">Tops</a>
            </div>
        <?php } else { ?>
            <div class="text-center text-muted p-40">No hay datos de clasificación disponibles.</div>
        <?php } ?>
    </div>

    <!-- Contenido: tabla de equipos -->
    <div class="<?= $clase_contenido_equipos ?>" id="tab-equipos">
        <?php if (!empty($cEquipos)) { ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Equipo</th>
                            <th class="text-right">Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cEquipos as $i => $eq) { ?>
                            <?php $pos = $i + 1; ?>
                            <tr>
                                <td class="<?= clasePosicion($pos) ?> fw-900">
                                    <?= medallaPosicion($pos) ?> <?= $pos ?>
                                </td>
                                <td>
                                    <strong><?= e($eq['nombre']) ?></strong>
                                </td>
                                <td class="points-cell">
                                    <?= $eq['total_puntos'] ?> <span class="text-muted points-unit">pts</span>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-12">
                <a href="<?= SITE_URL ?>tops.php" class="btn btn-accent btn-sm">Tops</a>
            </div>
        <?php } else { ?>
            <div class="text-center text-muted p-40">No hay datos de clasificación de equipos disponibles.</div>
        <?php } ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
