<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$temporada = temporadaActiva($pdo);
$carrera_id = 0;
if (isset($_GET['carrera']) && $_GET['carrera'] !== '') {
    $carrera_id = (int)$_GET['carrera'];
}

$texto_temporada = 'No hay temporada activa';
if ($temporada) {
    $texto_temporada = $temporada['nombre'];
}

// Sacamos las carreras terminadas para mostrarlas en el selector
$carrerasFinalizadas = [];
if ($temporada) {
    $stmt = $pdo->prepare("SELECT * FROM carreras WHERE temporada = ? AND estado = 'terminada' ORDER BY fecha DESC");
    $stmt->execute([$temporada['id']]);
    $carrerasFinalizadas = $stmt->fetchAll();
}

// Si el usuario eligió una carrera, cargamos sus resultados
$resultados = [];
$carreraActual = null;
if ($carrera_id) {
    $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id = ?");
    $stmt->execute([$carrera_id]);
    $carreraActual = $stmt->fetch();

    $stmt = $pdo->prepare(
        "SELECT r.*, u.nombre_usuario, e.nombre AS equipo,
                (SELECT GROUP_CONCAT(s.tipo SEPARATOR ', ') 
                 FROM sanciones s 
                 WHERE s.usuario = r.usuario AND s.carrera = r.carrera) AS sanciones_resumen
         FROM resultados r
         JOIN usuarios u ON r.usuario = u.id
         LEFT JOIN equipos e ON u.equipo = e.id
         WHERE r.carrera = ?
         ORDER BY r.posicion ASC"
    );
    $stmt->execute([$carrera_id]);
    $resultados = $stmt->fetchAll();
}

$titulo_pagina = 'Resultados — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-40">
    <div class="text-center mb-3">
        <h1>🏁 Resultados</h1>
        <p class="text-muted"><?= e($texto_temporada) ?></p>
    </div>

    <?php if (esAdmin()) { ?>
        <div class="text-center mb-3">
            <a href="<?= SITE_URL ?>admin/resultados.php" class="btn btn-admin">⚙️ Publicar resultados</a>
        </div>
    <?php } ?>

    <!-- Selector de carrera (solo aparecen carreras terminadas) -->
    <?php if (!empty($carrerasFinalizadas)) { ?>
        <div class="text-center mb-3">
            <form method="GET" class="inline-flex gap-10 items-center flex-wrap justify-center">
                <label class="text-muted fs-13 fw-600">Seleccionar carrera:</label>
                <select name="carrera" onchange="this.form.submit()" class="select-inline">
                    <option value="">— Elige una carrera —</option>
                    <?php foreach ($carrerasFinalizadas as $cf) { ?>
                        <?php
                        $selected = '';
                        if ($carrera_id == $cf['id']) {
                            $selected = 'selected';
                        }
                        ?>
                        <option value="<?= $cf['id'] ?>" <?= $selected ?>>
                            <?= e($cf['nombre']) ?> — <?= fechaCorta($cf['fecha']) ?>
                        </option>
                    <?php } ?>
                </select>
            </form>
        </div>
    <?php } ?>

    <?php if ($carreraActual && !empty($resultados)) { ?>
        <?php
        $tipoCarrera = '';
        if (isset($carreraActual['tipo']) && $carreraActual['tipo'] !== '') {
            $tipoCarrera = $carreraActual['tipo'];
        }

        $textoTipo = 'Vueltas';
        if ($tipoCarrera === 'resistencia') {
            $textoTipo = 'Resistencia';
        }
        ?>
        <h2 class="text-center mb-2"><?= e($carreraActual['nombre']) ?></h2>
        <p class="text-center text-muted mb-3 fs-14">
            🏁 <?= e($textoTipo) ?> — <?= fechaCorta($carreraActual['fecha']) ?>
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Piloto</th>
                        <th>Equipo</th>
                        <th>Tiempo</th>
                        <th class="text-right">Puntos</th>
                        <th>Sanciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $r) { ?>
                        <?php
                        $esYo = estaLogueado() && $_SESSION['usuario_id'] == $r['usuario'];
                        $claseFila = '';
                        if ($esYo) {
                            $claseFila = 'row-highlight';
                        }

                        $textoSanciones = '';
                        if (!empty($r['sanciones_resumen'])) {
                            $textoSanciones = e($r['sanciones_resumen']);
                        }
                        ?>
                        <tr class="<?= $claseFila ?>">
                            <td class="<?= clasePosicion($r['posicion']) ?> fw-900">
                                <?= medallaPosicion($r['posicion']) . ' ' . $r['posicion'] ?>
                            </td>
                            <td><strong><?= e($r['nombre_usuario']) ?></strong></td>
                            <td>
                                <?php if ($r['equipo']) { ?>
                                    <span class="text-accent"><?= e($r['equipo']) ?></span>
                                <?php } else { ?>
                                    <span class="text-muted">—</span>
                                <?php } ?>
                            </td>
                            <td class="mono-time">
                                <?= formatoTiempo((int)$r['tiempo_total']) ?>
                            </td>
                            <td class="points-cell">
                                <?= $r['puntos'] ?> <span class="text-muted points-unit">pts</span>
                            </td>
                            <td class="text-danger sanciones-cell">
                                <?= $textoSanciones ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } elseif ($carrera_id) { ?>
        <div class="text-center text-muted p-40">No hay resultados para esta carrera.</div>
    <?php } else { ?>
        <div class="text-center text-muted p-40">Selecciona una carrera para ver los resultados.</div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
