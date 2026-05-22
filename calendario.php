<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$temporada = temporadaActiva($pdo);
$carreras = [];
if ($temporada) {
    $usuario_id = 0;
    if (estaLogueado()) {
        $usuario_id = $_SESSION['usuario_id'];
    }
    $stmt = $pdo->prepare(
        "SELECT c.*,
                (SELECT COUNT(*) FROM inscripciones i WHERE i.carrera = c.id AND i.estado = 'inscrito') AS num_inscritos,
                (SELECT COUNT(*) FROM inscripciones i WHERE i.carrera = c.id AND i.usuario = ? AND i.estado = 'inscrito') AS ya_inscrito
         FROM carreras c
         WHERE c.temporada = ?
         ORDER BY c.fecha ASC"
    );
    $stmt->execute([$usuario_id, $temporada['id']]);
    $carreras = $stmt->fetchAll();
}

$titulo_pagina = 'Calendario — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-40">
    <div class="text-center mb-3">
        <h1>📅 Calendario</h1>
        <?php if ($temporada) { ?>
            <p class="text-muted"><?= e($temporada['nombre']) ?></p>
        <?php } ?>
    </div>

    <?php if (esAdmin()) { ?>
        <div class="text-center mb-3">
            <a href="<?= SITE_URL ?>admin/carreras.php" class="btn btn-admin">⚙️ Gestionar carreras</a>
        </div>
    <?php } ?>

    <?php if (!empty($carreras)) { ?>
        <div class="grid-2">
            <?php foreach ($carreras as $c) { ?>
                <?php
                $esFutura = strtotime($c['fecha'] . ' 23:59:59') > time();
                switch ($c['estado']) {
                    case 'terminada':
                        $estadoBadge = '<span class="badge badge-success">Finalizada</span>';
                        break;
                    default:
                        if ($esFutura) {
                            $estadoBadge = '<span class="badge badge-accent">Próximamente</span>';
                        } else {
                            $estadoBadge = '<span class="badge badge-gold">En curso</span>';
                        }
                        break;
                }

                switch ($c['tipo']) {
                    case 'resistencia':
                        $tipoBadge = '<span class="badge badge-danger">Resistencia</span>';
                        break;
                    default:
                        $tipoBadge = '<span class="badge badge-accent">Vueltas</span>';
                        break;
                }
                ?>
                <div class="card card-accent">
                    <div class="flex-between mb-12">
                        <?= $estadoBadge ?> <?= $tipoBadge ?>
                    </div>
                    <h3 class="mb-6"><?= e($c['nombre']) ?></h3>
                    <p class="text-muted fs-13">
                        📅 <?= fechaHora($c['fecha']) ?>
                    </p>
                    <div class="mt-12 fs-12">
                        <span class="calendario-inscritos">👥 <?= $c['num_inscritos'] ?> inscritos</span>
                    </div>

                    <?php if ($esFutura && $c['estado'] === 'pendiente') { ?>
                        <?php if (estaLogueado()) { ?>
                            <?php if ($c['ya_inscrito']) { ?>
                                <form method="POST" action="<?= SITE_URL ?>inscripcion.php" class="mt-12">
                                    <input type="hidden" name="carrera_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="accion" value="retirar">
                                    <button type="submit" class="btn btn-danger btn-sm btn-block" onclick="return confirm('¿Seguro que quieres retirarte?')">❌ Retirarme</button>
                                </form>
                            <?php } else { ?>
                                <form method="POST" action="<?= SITE_URL ?>inscripcion.php" class="mt-12">
                                    <input type="hidden" name="carrera_id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-accent btn-sm btn-block">🏎️ Inscribirme</button>
                                </form>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($c['estado'] === 'terminada') { ?>
                        <a href="<?= SITE_URL ?>resultados.php?carrera=<?= $c['id'] ?>" class="btn btn-gold btn-sm btn-block mt-1">📊 Ver resultados</a>
                    <?php } ?>

                    <?php if ($esFutura && $c['estado'] === 'pendiente') { ?>
                        <div class="countdown countdown--start mt-15" id="cd-<?= $c['id'] ?>"></div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                iniciarCountdown('<?= $c['fecha'] ?> 23:59:59', 'cd-<?= $c['id'] ?>');
                            });
                        </script>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="text-center text-muted p-40">No hay carreras programadas.</div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
