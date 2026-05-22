<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requiereAdmin();

$sanciones = $pdo->query(
    "SELECT s.*, u.nombre_usuario, c.nombre AS carrera_nombre
     FROM sanciones s
     JOIN usuarios u ON s.usuario = u.id
     LEFT JOIN carreras c ON s.carrera = c.id
     ORDER BY s.fecha DESC"
)->fetchAll();

$titulo_pagina = 'Sanciones — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>⚠️ Sanciones</h1>
    <p>Registro de penalizaciones e infracciones</p>
</div>

<div class="container pb-40">
    <div class="text-center mb-3">
        <a href="<?= SITE_URL ?>admin/sanciones.php" class="btn btn-admin">⚙️ Gestionar sanciones</a>
    </div>

    <?php if (!empty($sanciones)) { ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Piloto</th>
                        <th>Carrera</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sanciones as $s) { ?>
                        <?php
                        $esYo = estaLogueado() && $_SESSION['usuario_id'] == $s['usuario'];
                        $claseFila = '';
                        if ($esYo) {
                            $claseFila = 'row-highlight';
                        }
                        $carreraNombre = '—';
                        if (isset($s['carrera_nombre']) && $s['carrera_nombre'] !== '') {
                            $carreraNombre = $s['carrera_nombre'];
                        }
                        $descripcion = '';
                        if (isset($s['descripcion']) && $s['descripcion'] !== '') {
                            $descripcion = $s['descripcion'];
                        }
                        ?>
                        <tr class="<?= $claseFila ?>">
                            <td class="text-muted"><?= fechaCorta($s['fecha']) ?></td>
                            <td><strong><?= e($s['nombre_usuario']) ?></strong></td>
                            <td><?= e($carreraNombre) ?></td>
                            <td><span class="badge badge-danger"><?= e($s['tipo']) ?></span></td>
                            <td class="text-muted fs-13"><?= e($descripcion) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="text-center p-40">
            <span class="fs-48">✅</span>
            <p class="text-muted mt-2">No hay sanciones registradas. ¡Competición limpia!</p>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
