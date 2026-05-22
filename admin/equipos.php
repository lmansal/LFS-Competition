<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';
$editando = null;

// Si viene ?eliminar=ID, borramos el equipo (y dejamos a sus pilotos sin equipo)
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Primero ponemos equipo = NULL a los usuarios de ese equipo para no romper la FK
    $pdo->prepare("UPDATE usuarios SET equipo = NULL WHERE equipo = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM equipos WHERE id = ?")->execute([$id]);
    header('Location: ' . SITE_URL . 'admin/equipos.php?msg=' . urlencode('success:Equipo eliminado.'));
    exit;
}

// Si viene ?editar=ID, cargamos el equipo en el formulario
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM equipos WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

// Guardar el formulario (crea o actualiza)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = 0;
    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int)$_POST['id'];
    }
    $nombre = '';
    if (isset($_POST['nombre'])) {
        $nombre = trim($_POST['nombre']);
    }

    if (!$nombre) {
        $msg = 'error:El nombre es obligatorio.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE equipos SET nombre = ? WHERE id = ?");
            $stmt->execute([$nombre, $id]);
            $msg = 'success:Equipo actualizado.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO equipos (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            $msg = 'success:Equipo creado.';
        }
    }
}

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
}
// Listado de equipos con número de pilotos y el nombre del jefe (si existe)
$equipos = $pdo->query(
    "SELECT e.*, 
            (SELECT COUNT(*) FROM usuarios u WHERE u.equipo = e.id) AS num_pilotos,
            (SELECT nombre_usuario FROM usuarios u WHERE u.equipo = e.id AND u.rango_piloto = 'jefe_equipo' LIMIT 1) AS nombre_jefe
     FROM equipos e ORDER BY e.nombre"
)->fetchAll();

$titulo_pagina = 'Admin: Equipos — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>🛡️ Gestión de Equipos</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <?php
    $titulo_form = 'Crear nuevo equipo';
    $texto_boton = 'Crear equipo';
    $valor_id = 0;
    $valor_nombre = '';

    if ($editando) {
        $titulo_form = 'Editar equipo';
        $texto_boton = 'Actualizar';
        if (isset($editando['id'])) {
            $valor_id = (int)$editando['id'];
        }
        if (isset($editando['nombre'])) {
            $valor_nombre = $editando['nombre'];
        }
    }
    ?>

    <!-- Formulario para crear/editar un equipo -->
    <div class="form-card mb-3">
        <h2><?= $titulo_form ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $valor_id ?>">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" required value="<?= e($valor_nombre) ?>">
            </div>
            <div class="mt-20 flex gap-10">
                <button type="submit" class="btn-submit m-0 flex-1"><?= $texto_boton ?></button>
                <?php if ($editando) { ?>
                    <a href="<?= SITE_URL ?>admin/equipos.php" class="btn btn-accent flex-1 flex-center">Cancelar</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Jefe de Equipo</th><th>Pilotos</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($equipos as $eq) { ?>
                    <tr>
                        <td class="text-muted"><?= $eq['id'] ?></td>
                        <td><strong class="text-success"><?= e($eq['nombre']) ?></strong></td>
                        <td>
                            <?php if ($eq['nombre_jefe']) { ?>
                                <span class="badge badge-success"><?= e($eq['nombre_jefe']) ?></span>
                            <?php } else { ?>
                                <span class="text-muted fs-12">Sin asignar</span>
                            <?php } ?>
                        </td>
                        <td><?= $eq['num_pilotos'] ?></td>
                        <td>
                            <a href="?editar=<?= $eq['id'] ?>" class="btn btn-accent fs-12">✏️</a>
                            <a href="?eliminar=<?= $eq['id'] ?>" class="btn btn-danger fs-12" onclick="return confirm('¿Eliminar este equipo?')">🗑️</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
