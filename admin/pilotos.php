<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';
$editando = null;

// Si viene ?eliminar=ID, borramos ese piloto y sus datos relacionados
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Dejamos el equipo a NULL y borramos inscripciones/resultados/sanciones para evitar datos huérfanos
    $pdo->prepare("UPDATE usuarios SET equipo = NULL WHERE id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM inscripciones WHERE usuario = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM resultados WHERE usuario = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM sanciones WHERE usuario = ?")->execute([$id]); 
    $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rango_piloto != 'admin'")->execute([$id]);
    header('Location: ' . SITE_URL . 'admin/pilotos.php?msg=' . urlencode('success:Piloto eliminado.'));
    exit;
}

// Si viene ?editar=ID, cargamos ese piloto para editarlo
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

// Guardar cambios del formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $rango_piloto = 'piloto';
    if (isset($_POST['rango_piloto']) && $_POST['rango_piloto'] !== '') {
        $rango_piloto = $_POST['rango_piloto'];
    }
    $etiquetas = '';
    if (isset($_POST['etiquetas'])) {
        $etiquetas = $_POST['etiquetas'];
    }
    $equipo = null;
    if (!empty($_POST['equipo'])) {
        $equipo = (int)$_POST['equipo'];
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET rango_piloto = ?, etiquetas = ?, equipo = ? WHERE id = ?");
    $stmt->execute([$rango_piloto, $etiquetas, $equipo, $id]);
    $msg = 'success:Piloto actualizado.';
    $editando = null;
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
$equipos = $pdo->query("SELECT * FROM equipos ORDER BY nombre")->fetchAll();
$pilotos = $pdo->query(
    "SELECT u.*, e.nombre AS equipo_nombre
     FROM usuarios u LEFT JOIN equipos e ON u.equipo = e.id
     WHERE u.rango_piloto != 'admin'
     ORDER BY u.nombre_usuario"
)->fetchAll();

$titulo_pagina = 'Admin: Pilotos — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>👤 Gestión de Pilotos</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php if ($editando) { ?>
        <?php
        $valorRango = 'piloto';
        if (isset($editando['rango_piloto']) && $editando['rango_piloto'] !== '') {
            $valorRango = $editando['rango_piloto'];
        }
        $valorEquipo = 0;
        if (isset($editando['equipo']) && $editando['equipo']) {
            $valorEquipo = (int)$editando['equipo'];
        }
        $valorEtiquetas = '';
        if (isset($editando['etiquetas']) && $editando['etiquetas'] !== '') {
            $valorEtiquetas = $editando['etiquetas'];
        }

        $selectedPiloto = '';
        if ($valorRango === 'piloto') {
            $selectedPiloto = 'selected';
        }
        $selectedJefe = '';
        if ($valorRango === 'jefe_equipo') {
            $selectedJefe = 'selected';
        }
        $selectedAdmin = '';
        if ($valorRango === 'admin') {
            $selectedAdmin = 'selected';
        }
        ?>
        <div class="form-card mb-3">
            <h2>Editar: <?= e($editando['nombre_usuario']) ?></h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $editando['id'] ?>">
                <div class="grid-2">
                    <div class="form-group">
                        <label>Rango de Piloto</label>
                        <select name="rango_piloto">
                            <option value="piloto" <?= $selectedPiloto ?>>Piloto</option>
                            <option value="jefe_equipo" <?= $selectedJefe ?>>Jefe de Equipo</option>
                            <option value="admin" <?= $selectedAdmin ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Equipo</label>
                        <select name="equipo">
                            <option value="">Sin equipo</option>
                            <?php foreach ($equipos as $eq) { ?>
                                <?php
                                $selectedEq = '';
                                if ($valorEquipo == $eq['id']) {
                                    $selectedEq = 'selected';
                                }
                                ?>
                                <option value="<?= $eq['id'] ?>" <?= $selectedEq ?>><?= e($eq['nombre']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Etiquetas (separadas por comas)</label>
                        <input type="text" name="etiquetas" value="<?= e($valorEtiquetas) ?>" placeholder="Ej: FIA, Coordinador, Novato">
                    </div>
                </div>
                <div class="mt-20 flex gap-10">
                    <button type="submit" class="btn-submit m-0 flex-1">Actualizar</button>
                    <a href="<?= SITE_URL ?>admin/pilotos.php" class="btn btn-accent flex-1 flex-center">Cancelar</a>
                </div>
            </form>
        </div>
    <?php } ?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>ID</th><th>Nick</th><th>Rango / Etiquetas</th><th>Equipo</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pilotos as $p) { ?>
                    <tr>
                        <td class="text-muted"><?= $p['id'] ?></td>
                        <td><strong><?= e($p['nombre_usuario']) ?></strong></td>
                        <td>
                            <?php
                            $rangoFila = 'piloto';
                            if (isset($p['rango_piloto']) && $p['rango_piloto'] !== '') {
                                $rangoFila = $p['rango_piloto'];
                            }
                            $equipoFila = '—';
                            if (isset($p['equipo_nombre']) && $p['equipo_nombre'] !== '') {
                                $equipoFila = $p['equipo_nombre'];
                            }
                            ?>
                            <span class="badge badge-accent fs-10"><?= e($rangoFila) ?></span>
                            <?php if (!empty($p['etiquetas'])) { ?>
                                <?php foreach(explode(',', $p['etiquetas']) as $tag) { ?>
                                    <span class="badge badge-soft"><?= e(trim($tag)) ?></span>
                                <?php } ?>
                            <?php } ?>
                        </td>
                        <td class="text-success"><?= e($equipoFila) ?></td>
                        <td>
                            <a href="?editar=<?= $p['id'] ?>" class="btn btn-accent fs-12">✏️</a>
                            <a href="?eliminar=<?= $p['id'] ?>" class="btn btn-danger fs-12" onclick="return confirm('¿Eliminar piloto?')">🗑️</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
