<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';
$editando = null;

// Si viene ?eliminar=ID por la URL, borramos esa carrera (y lo relacionado)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Primero borramos inscripciones/resultados/sanciones para no dejar datos sueltos
    $pdo->prepare("DELETE FROM inscripciones WHERE carrera = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM resultados WHERE carrera = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM sanciones WHERE carrera = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM carreras WHERE id = ?")->execute([$id]);
    header('Location: ' . SITE_URL . 'admin/carreras.php?msg=' . urlencode('success:Carrera eliminada.'));
    exit;
}

// Si viene ?editar=ID, cargamos esa carrera para rellenar el formulario
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

// Guardar (sirve tanto para crear como para actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = 0;
    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int)$_POST['id'];
    }
    $temporada = (int)$_POST['temporada'];
    $nombre = '';
    if (isset($_POST['nombre'])) {
        $nombre = trim($_POST['nombre']);
    }
    $fecha = '';
    if (isset($_POST['fecha'])) {
        $fecha = $_POST['fecha'];
    }
    $tipo = 'vueltas';
    if (isset($_POST['tipo']) && $_POST['tipo'] !== '') {
        $tipo = $_POST['tipo'];
    }
    $estado = 'pendiente';
    if (isset($_POST['estado']) && $_POST['estado'] !== '') {
        $estado = $_POST['estado'];
    }

    if (!$nombre || !$fecha || !$temporada) {
        $msg = 'error:Nombre, fecha y temporada son obligatorios.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE carreras SET temporada=?, nombre=?, fecha=?, tipo=?, estado=? WHERE id=?");
            $stmt->execute([$temporada, $nombre, $fecha, $tipo, $estado, $id]);
            $msg = 'success:Carrera actualizada.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO carreras (temporada, nombre, fecha, tipo, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$temporada, $nombre, $fecha, $tipo, $estado]);
            $msg = 'success:Carrera creada.';
        }
    }
}

if (isset($_GET['msg'])){
    $msg = urldecode($_GET['msg']);
}

$temporadas = $pdo->query("SELECT * FROM temporadas ORDER BY fecha_inicio DESC")->fetchAll();
// Listado de carreras con el número de inscritos
$carreras = $pdo->query(
    "SELECT c.*, t.nombre AS temporada_nombre,
            (SELECT COUNT(*) FROM inscripciones i WHERE i.carrera = c.id AND i.estado = 'inscrito') AS num_inscritos
     FROM carreras c
     JOIN temporadas t ON c.temporada = t.id
     ORDER BY c.fecha DESC"
)->fetchAll();

$titulo_pagina = 'Admin: Carreras — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>🏁 Gestión de Carreras</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php
    $titulo_form = 'Crear nueva carrera';
    $texto_boton = 'Crear carrera';
    $valor_id = 0;
    $valor_nombre = '';
    $valor_temporada = 0;
    $valor_fecha = '';
    $valor_tipo = 'vueltas';
    $valor_estado = 'pendiente';

    if ($editando) {
        $titulo_form = 'Editar carrera';
        $texto_boton = 'Actualizar';

        if (isset($editando['id'])) {
            $valor_id = (int)$editando['id'];
        }
        if (isset($editando['nombre'])) {
            $valor_nombre = $editando['nombre'];
        }
        if (isset($editando['temporada'])) {
            $valor_temporada = (int)$editando['temporada'];
        }
        if (isset($editando['fecha'])) {
            $valor_fecha = date('Y-m-d', strtotime($editando['fecha']));
        }
        if (isset($editando['tipo']) && $editando['tipo'] !== '') {
            $valor_tipo = $editando['tipo'];
        }
        if (isset($editando['estado']) && $editando['estado'] !== '') {
            $valor_estado = $editando['estado'];
        }
    }
    ?>

    <div class="form-card mb-3">
        <h2><?= $titulo_form ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $valor_id ?>">
            <div class="grid-2">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" required value="<?= e($valor_nombre) ?>" placeholder="GP de España">
                </div>
                <div class="form-group">
                    <label>Temporada *</label>
                    <select name="temporada" required>
                        <?php foreach ($temporadas as $t) { ?>
                            <?php
                            $selected = '';
                            if ($valor_temporada == $t['id']) {
                                $selected = 'selected';
                            }
                            ?>
                            <option value="<?= $t['id'] ?>" <?= $selected ?>><?= e($t['nombre']) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha *</label>
                    <input type="date" name="fecha" required value="<?= $valor_fecha ?>">
                </div>
                <div class="form-group">
                    <label>Tipo</label>
                    <select name="tipo">
                        <?php
                        $selectedVueltas = '';
                        if ($valor_tipo === 'vueltas') {
                            $selectedVueltas = 'selected';
                        }
                        $selectedResistencia = '';
                        if ($valor_tipo === 'resistencia') {
                            $selectedResistencia = 'selected';
                        }
                        ?>
                        <option value="vueltas" <?= $selectedVueltas ?>>Vueltas</option>
                        <option value="resistencia" <?= $selectedResistencia ?>>Resistencia</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado">
                    <?php
                    $selectedPendiente = '';
                    if ($valor_estado === 'pendiente') {
                        $selectedPendiente = 'selected';
                    }
                    $selectedTerminada = '';
                    if ($valor_estado === 'terminada') {
                        $selectedTerminada = 'selected';
                    }
                    ?>
                    <option value="pendiente" <?= $selectedPendiente ?>>Pendiente</option>
                    <option value="terminada" <?= $selectedTerminada ?>>Finalizada</option>
                </select>
            </div>
            <div class="mt-20 flex gap-10">
                <button type="submit" class="btn-submit m-0 flex-1"><?= $texto_boton ?></button>
                <?php if ($editando) { ?>
                    <a href="<?= SITE_URL ?>admin/carreras.php" class="btn btn-accent flex-1 flex-center">Cancelar</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead><tr><th>ID</th><th>Carrera</th><th>Fecha</th><th>Temporada</th><th>Inscritos</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($carreras as $c) { ?>
                    <tr>
                        <td class="text-muted"><?= $c['id'] ?></td>
                        <td><strong><?= e($c['nombre']) ?></strong></td>
                        <td><?= fechaHora($c['fecha']) ?></td>
                        <td class="text-accent"><?= e($c['temporada_nombre']) ?></td>
                        <td><?= $c['num_inscritos'] ?></td>
                        <td>
                            <?php if ($c['estado'] === 'terminada') { ?>
                                <span class="badge badge-success">Finalizada</span>
                            <?php } else { ?>
                                <span class="badge">Pendiente</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="?editar=<?= $c['id'] ?>" class="btn btn-accent fs-12">✏️</a>
                            <a href="?eliminar=<?= $c['id'] ?>" class="btn btn-danger fs-12" onclick="return confirm('¿Eliminar carrera?')">🗑️</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
