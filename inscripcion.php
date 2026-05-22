<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requiereLogin();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrera_id = 0;
    if (isset($_POST['carrera_id']) && $_POST['carrera_id'] !== '') {
        $carrera_id = (int)$_POST['carrera_id'];
    }
    $usuario_id = $_SESSION['usuario_id'];
    $accion = 'inscribir';
    if (isset($_POST['accion']) && $_POST['accion'] !== '') {
        $accion = $_POST['accion'];
    }

    if ($accion === 'retirar') {
        // Si el piloto se retira, marcamos su inscripción como "no_asiste" (sin borrar histórico)
        $stmt = $pdo->prepare("UPDATE inscripciones SET estado = 'no_asiste' WHERE usuario = ? AND carrera = ? AND estado = 'inscrito'");
        $stmt->execute([$usuario_id, $carrera_id]);
        $msg = 'success:Te has retirado de la carrera. 🏁';
    } else {
        // Comprobamos que la carrera exista, esté pendiente y sea hoy o futura
        $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id = ? AND estado = 'pendiente' AND fecha >= CURDATE()");
        $stmt->execute([$carrera_id]);
        $carrera = $stmt->fetch();

        if (!$carrera) {
            $msg = 'error:La carrera no está disponible para inscripción.';
        } else {
            // Si ya existe inscripción, decidimos si es re-inscripción o si ya está inscrito
            $stmt = $pdo->prepare("SELECT id, estado FROM inscripciones WHERE usuario = ? AND carrera = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$usuario_id, $carrera_id]);
            $insc = $stmt->fetch();
            $estadoInsc = '';
            if ($insc && isset($insc['estado'])) {
                $estadoInsc = $insc['estado'];
            }
            if ($insc && $estadoInsc === 'inscrito') {
                $msg = 'error:Ya estás inscrito en esta carrera.';
            } elseif ($insc && $estadoInsc === 'no_asiste') {
                $stmt = $pdo->prepare("UPDATE inscripciones SET estado = 'inscrito' WHERE id = ?");
                $stmt->execute([(int)$insc['id']]);
                $msg = 'success:¡Inscripción reactivada! 🏁';
            } else {
                // Si todo OK, insertamos la inscripción
                $stmt = $pdo->prepare("INSERT INTO inscripciones (usuario, carrera, estado) VALUES (?, ?, 'inscrito')");
                $stmt->execute([$usuario_id, $carrera_id]);
                $msg = 'success:¡Inscripción confirmada! Nos vemos en la pista. 🏁';
            }
        }
    }
}

// Sacamos la lista de carreras a las que se puede apuntar (futuras y pendientes)
$temporada = temporadaActiva($pdo);
$carrerasAbiertas = [];
if ($temporada) {
    $stmt = $pdo->prepare(
        "SELECT c.*,
                (SELECT COUNT(*) FROM inscripciones i WHERE i.carrera = c.id AND i.estado = 'inscrito') AS num_inscritos,
                (SELECT COUNT(*) FROM inscripciones i WHERE i.carrera = c.id AND i.usuario = ? AND i.estado = 'inscrito') AS ya_inscrito
         FROM carreras c
         WHERE c.temporada = ? AND c.estado = 'pendiente' AND c.fecha >= CURDATE()
         ORDER BY c.fecha ASC"
    );
    $stmt->execute([$_SESSION['usuario_id'], $temporada['id']]);
    $carrerasAbiertas = $stmt->fetchAll();
}

$titulo_pagina = 'Inscripción — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-40">
        <h1>🏎️ Inscripción en Carreras</h1>
        <p class="text-muted">Apúntate a las próximas carreras de la temporada</p>
    </div>
</div>

<div class="container max-w-700 pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php if (!empty($carrerasAbiertas)) { ?>
        <?php foreach ($carrerasAbiertas as $c) { ?>
            <div class="card card-accent mb-2">
                <div class="flex-between flex-wrap gap-15">
                    <div>
                        <h3 class="mb-4"><?= e($c['nombre']) ?></h3>
                        <p class="text-muted fs-13">📅 <?= fechaHora($c['fecha']) ?></p>
                        <span class="text-accent fs-12 fw-700">👥 <?= $c['num_inscritos'] ?> inscritos</span>
                    </div>
                    <div>
                        <?php if ($c['ya_inscrito']) { ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="carrera_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="accion" value="retirar">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Seguro que quieres retirarte?')">Retirarme ❌</button>
                            </form>
                            <span class="badge badge-success badge-lg mt-5">✅ Inscrito</span>
                        <?php } else { ?>
                            <form method="POST">
                                <input type="hidden" name="carrera_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="accion" value="inscribir">
                                <button type="submit" class="btn btn-accent">Inscribirme 🏁</button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="card text-center closed-box">
            <div class="fs-48 mb-20">🔒</div>
            <h2>No hay carreras abiertas</h2>
            <p class="text-muted">No hay carreras pendientes en este momento.</p>
            <a href="<?= SITE_URL ?>calendario.php" class="btn btn-accent mt-2">📅 Ver calendario</a>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
