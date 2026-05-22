<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';

// Si viene ?aprobar=ID, aprobamos esa solicitud (sistema antiguo)
if (isset($_GET['aprobar'])) {
    $uid = (int)$_GET['aprobar'];
    // Solo aprobamos si la invitación sigue en estado 'invitado'
    $stmt = $pdo->prepare("SELECT solicitud_equipo FROM usuarios WHERE id = ? AND estado_solicitud = 'invitado'");
    $stmt->execute([$uid]);
    $sol = $stmt->fetch();
    if ($sol) {
        // Metemos al usuario en el equipo y limpiamos la solicitud
        $pdo->prepare("UPDATE usuarios SET equipo = ?, solicitud_equipo = NULL, estado_solicitud = 'aprobada' WHERE id = ?")
            ->execute([$sol['solicitud_equipo'], $uid]);
        $msg = 'success:Solicitud aprobada.';
    }
}

// Si viene ?rechazar=ID, rechazamos esa solicitud
if (isset($_GET['rechazar'])) {
    $uid = (int)$_GET['rechazar'];
    // Quitamos el equipo solicitado y dejamos el estado como 'rechazada'
    $pdo->prepare("UPDATE usuarios SET solicitud_equipo = NULL, estado_solicitud = 'rechazada' WHERE id = ?")
        ->execute([$uid]);
    $msg = 'success:Solicitud rechazada.';
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}

$solicitudes = $pdo->query(
    "SELECT u.id, u.nombre_usuario, u.estado_solicitud, e.nombre AS equipo_nombre
     FROM usuarios u
     JOIN equipos e ON u.solicitud_equipo = e.id
     WHERE u.estado_solicitud = 'invitado'
     ORDER BY u.id"
)->fetchAll();

$titulo_pagina = 'Admin: Solicitudes — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>📨 Solicitudes de Equipo</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container max-w-800 pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php if (!empty($solicitudes)) { ?>
        <?php foreach ($solicitudes as $sol) { ?>
            <div class="card card-accent mb-2">
                <div class="flex-between items-center flex-wrap gap-15">
                    <div>
                        <strong><?= e($sol['nombre_usuario']) ?></strong>
                        <span class="text-muted fs-13">quiere unirse a</span>
                        <strong class="text-success"><?= e($sol['equipo_nombre']) ?></strong>
                    </div>
                    <div class="flex gap-1">
                        <a href="?aprobar=<?= $sol['id'] ?>" class="btn btn-success fs-13">✅ Aprobar</a>
                        <a href="?rechazar=<?= $sol['id'] ?>" class="btn btn-danger fs-13">❌ Rechazar</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="text-center p-40">
            <span class="fs-48">✅</span>
            <p class="text-muted mt-2">No hay solicitudes pendientes.</p>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
