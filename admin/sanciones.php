<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';
$editando = null;

// Si viene ?eliminar=ID, borramos esa sanción
if (isset($_GET['eliminar'])) {
    $pdo->prepare("DELETE FROM sanciones WHERE id = ?")->execute([(int)$_GET['eliminar']]);
    header('Location: /admin/sanciones.php?msg=' . urlencode('success:Sanción eliminada.'));
    exit;
}

// Si viene ?editar=ID, cargamos la sanción para editarla en el formulario
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM sanciones WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

    // Guardar (si viene id > 0, actualiza; si no, crea una nueva)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = 0;
        if (isset($_POST['id']) && $_POST['id'] !== '') {
            $id = (int)$_POST['id'];
        }
        $usuario = (int)$_POST['usuario'];
        $carrera = null;
        if (!empty($_POST['carrera'])) {
            $carrera = (int)$_POST['carrera'];
        }
        $tipo = '';
        if (isset($_POST['tipo'])) {
            $tipo = trim($_POST['tipo']);
        }
        $desc = '';
        if (isset($_POST['descripcion'])) {
            $desc = trim($_POST['descripcion']);
        }

        if (!$usuario || !$tipo) {
            $msg = 'error:Piloto y tipo son obligatorios.';
        } else {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE sanciones SET usuario=?, carrera=?, tipo=?, descripcion=? WHERE id=?");
                $stmt->execute([$usuario, $carrera, $tipo, $desc, $id]);
                $msg = 'success:Sanción actualizada.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO sanciones (usuario, carrera, tipo, descripcion) VALUES (?, ?, ?, ?)");
                $stmt->execute([$usuario, $carrera, $tipo, $desc]);
                $msg = 'success:Sanción creada.';
            }
            $editando = null;
        }
    }

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
$pilotos = $pdo->query("SELECT id, nombre_usuario FROM usuarios WHERE rango_piloto != 'admin' ORDER BY nombre_usuario")->fetchAll();
$carreras = $pdo->query("SELECT id, nombre FROM carreras ORDER BY fecha DESC")->fetchAll();
$sanciones = $pdo->query(
    "SELECT s.*, u.nombre_usuario, c.nombre AS carrera_nombre
     FROM sanciones s
     JOIN usuarios u ON s.usuario = u.id
     LEFT JOIN carreras c ON s.carrera = c.id
     ORDER BY s.fecha DESC"
)->fetchAll();

$titulo_pagina = 'Admin: Sanciones — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>⚠️ Gestión de Sanciones</h1>
    <p><a href="/admin/" class="text-accent">← Panel Admin</a></p>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php
    $titulo_form = 'Nueva sanción';
    $texto_boton = 'Crear sanción';
    $valor_id = 0;
    $valor_usuario = 0;
    $valor_carrera = 0;
    $valor_tipo = '';
    $valor_desc = '';

    if ($editando) {
        $titulo_form = 'Editar sanción';
        $texto_boton = 'Actualizar';

        if (isset($editando['id'])) {
            $valor_id = (int)$editando['id'];
        }
        if (isset($editando['usuario'])) {
            $valor_usuario = (int)$editando['usuario'];
        }
        if (isset($editando['carrera']) && $editando['carrera']) {
            $valor_carrera = (int)$editando['carrera'];
        }
        if (isset($editando['tipo'])) {
            $valor_tipo = $editando['tipo'];
        }
        if (isset($editando['descripcion'])) {
            $valor_desc = $editando['descripcion'];
        }
    }
    ?>

    <div class="form-card mb-3">
        <h2><?= $titulo_form ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $valor_id ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Piloto *</label>
                    <select name="usuario" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($pilotos as $p) { ?>
                            <?php
                            $selected = '';
                            if ($valor_usuario == $p['id']) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="<?= $p['id'] ?>" <?= $selected ?>><?= e($p['nombre_usuario']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Carrera (opcional)</label>
                    <select name="carrera">
                        <option value="">— Sin carrera —</option>
                        <?php foreach ($carreras as $c) { ?>
                            <?php
                            $selected = '';
                            if ($valor_carrera == $c['id']) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="<?= $c['id'] ?>" <?= $selected ?>><?= e($c['nombre']) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Tipo *</label>
                    <select name="tipo" required>
                        <option value="">— Tipo —</option>
                        <?php foreach (['Aviso', 'Penalización de tiempo', 'Penalización de posiciones', 'DSQ', 'Suspensión'] as $t) { ?>
                            <?php
                            $selected = '';
                            if ($valor_tipo === $t) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="<?= $t ?>" <?= $selected ?>><?= $t ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion"><?= e($valor_desc) ?></textarea>
            </div>
            <button type="submit" class="btn-submit"><?= $texto_boton ?></button>
            <?php if ($editando) { ?>
                <a href="/admin/sanciones.php" class="btn btn-accent ml-8">Cancelar</a>
            <?php } ?>
        </form>
    </div>

    <?php if (!empty($sanciones)) { ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Fecha</th><th>Piloto</th><th>Carrera</th><th>Tipo</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($sanciones as $s) { ?>
                        <?php
                        $carreraNombre = '—';
                        if (isset($s['carrera_nombre']) && $s['carrera_nombre'] !== '') {
                            $carreraNombre = $s['carrera_nombre'];
                        }
                        ?>
                        <tr>
                            <td class="text-muted"><?= $s['id'] ?></td>
                            <td><?= fechaCorta($s['fecha']) ?></td>
                            <td><strong><?= e($s['nombre_usuario']) ?></strong></td>
                            <td class="text-muted"><?= e($carreraNombre) ?></td>
                            <td><span class="badge badge-danger"><?= e($s['tipo']) ?></span></td>
                            <td>
                                <a href="?editar=<?= $s['id'] ?>" class="btn btn-accent fs-12">✏️</a>
                                <a href="?eliminar=<?= $s['id'] ?>" class="btn btn-danger fs-12" onclick="return confirm('¿Eliminar sanción?')">🗑️</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
