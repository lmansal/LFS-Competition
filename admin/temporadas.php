<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';
$editando = null;

// Si viene ?eliminar=ID, borramos esa temporada (y sus carreras)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Borramos primero las carreras de esa temporada
    $pdo->prepare("DELETE FROM carreras WHERE temporada = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM temporadas WHERE id = ?")->execute([$id]);
    header('Location: ' . SITE_URL . 'admin/temporadas.php?msg=' . urlencode('success:Temporada eliminada.'));
    exit;
}

// Si viene ?editar=ID, cargamos la temporada para editarla
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM temporadas WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

// Guardar (si hay id, actualiza; si no, crea una temporada nueva)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = 0;
    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int)$_POST['id'];
    }
    $nombre = '';
    if (isset($_POST['nombre'])) {
        $nombre = trim($_POST['nombre']);
    }
    $fi = '';
    if (isset($_POST['fecha_inicio'])) {
        $fi = $_POST['fecha_inicio'];
    }
    $ff = '';
    if (isset($_POST['fecha_fin'])) {
        $ff = $_POST['fecha_fin'];
    }

    if (!$nombre || !$fi || !$ff) {
        $msg = 'error:Todos los campos son obligatorios.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE temporadas SET nombre = ?, fecha_inicio = ?, fecha_fin = ? WHERE id = ?");
            $stmt->execute([$nombre, $fi, $ff, $id]);
            $msg = 'success:Temporada actualizada.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO temporadas (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $fi, $ff]);
            $msg = 'success:Temporada creada.';
        }
    }
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
$temporadas = $pdo->query("SELECT * FROM temporadas ORDER BY fecha_inicio DESC")->fetchAll();

$titulo_pagina = 'Admin: Temporadas — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>📅 Gestión de Temporadas</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php
    $titulo_form = 'Crear nueva temporada';
    $texto_boton = 'Crear temporada';
    $valor_id = 0;
    $valor_nombre = '';
    $valor_fecha_inicio = '';
    $valor_fecha_fin = '';

    if ($editando) {
        $titulo_form = 'Editar temporada';
        $texto_boton = 'Actualizar';
        if (isset($editando['id'])) {
            $valor_id = (int)$editando['id'];
        }
        if (isset($editando['nombre'])) {
            $valor_nombre = $editando['nombre'];
        }
        if (isset($editando['fecha_inicio'])) {
            $valor_fecha_inicio = $editando['fecha_inicio'];
        }
        if (isset($editando['fecha_fin'])) {
            $valor_fecha_fin = $editando['fecha_fin'];
        }
    }
    ?>

    <div class="form-card mb-3">
        <h2><?= $titulo_form ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $valor_id ?>">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" required value="<?= e($valor_nombre) ?>" placeholder="Temporada 2025">
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha inicio *</label>
                    <input type="date" name="fecha_inicio" required value="<?= $valor_fecha_inicio ?>">
                </div>
                <div class="form-group">
                    <label>Fecha fin *</label>
                    <input type="date" name="fecha_fin" required value="<?= $valor_fecha_fin ?>">
                </div>
            </div>
            <div class="mt-20 flex gap-10">
                <button type="submit" class="btn-submit m-0 flex-1"><?= $texto_boton ?></button>
                <?php if ($editando) { ?>
                    <a href="<?= SITE_URL ?>admin/temporadas.php" class="btn btn-accent flex-1 flex-center">Cancelar</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Inicio</th><th>Fin</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($temporadas as $t) { ?>
                    <tr>
                        <td class="text-muted"><?= $t['id'] ?></td>
                        <td><strong><?= e($t['nombre']) ?></strong></td>
                        <td><?= fechaCorta($t['fecha_inicio']) ?></td>
                        <td><?= fechaCorta($t['fecha_fin']) ?></td>
                        <td>
                            <a href="?editar=<?= $t['id'] ?>" class="btn btn-accent fs-12">✏️</a>
                            <a href="?eliminar=<?= $t['id'] ?>" class="btn btn-danger fs-12" onclick="return confirm('¿Eliminar temporada y sus carreras?')">🗑️</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
