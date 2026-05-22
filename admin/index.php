<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();

$pilotos = contarRegistros($pdo, 'usuarios', "rango_piloto IN ('piloto','jefe_equipo')");
$equipos = contarRegistros($pdo, 'equipos');
$carreras = contarRegistros($pdo, 'carreras');
$solicitudes = contarRegistros($pdo, 'usuarios', "estado_solicitud = 'invitado'");
$inscripciones = contarRegistros($pdo, 'inscripciones', "estado = 'inscrito'");
$sanciones = contarRegistros($pdo, 'sanciones');

$clase_solicitudes = 'text-muted';
if ($solicitudes > 0) {
    $clase_solicitudes = 'text-danger';
}

$titulo_pagina = 'Panel Admin — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>⚙️ Panel de Administración</h1>
    <p>Gestión completa de la competición</p>
</div>

<div class="container pb-40">
    <!-- Resumen rápido (contadores) -->
    <div class="grid-4 mb-3">
        <div class="card text-center">
            <span class="fs-32">👤</span>
            <h2 class="text-accent"><?= $pilotos ?></h2>
            <p class="text-muted">Pilotos</p>
        </div>
        <div class="card text-center">
            <span class="fs-32">🛡️</span>
            <h2 class="text-success"><?= $equipos ?></h2>
            <p class="text-muted">Equipos</p>
        </div>
        <div class="card text-center">
            <span class="fs-32">🏁</span>
            <h2 class="text-gold"><?= $carreras ?></h2>
            <p class="text-muted">Carreras</p>
        </div>
        <div class="card text-center">
            <span class="fs-32">📨</span>
            <h2 class="<?= $clase_solicitudes ?>"><?= $solicitudes ?></h2>
            <p class="text-muted">Solicitudes</p>
        </div>
    </div>

    <!-- Accesos rápidos a las secciones del panel -->
    <h2 class="mb-2">Gestión rápida</h2>
    <div class="grid-3">
        <a href="<?= SITE_URL ?>admin/pilotos.php" class="card card-accent text-center">
            <span class="fs-36">👤</span>
            <h3>Pilotos</h3>
            <p class="text-muted fs-13">Ver, editar y gestionar pilotos</p>
        </a>
        <a href="<?= SITE_URL ?>admin/equipos.php" class="card card-accent text-center">
            <span class="fs-36">🛡️</span>
            <h3>Equipos</h3>
            <p class="text-muted fs-13">CRUD completo de equipos</p>
        </a>
        <a href="<?= SITE_URL ?>admin/solicitudes.php" class="card card-accent text-center">
            <span class="fs-36">📨</span>
            <h3>Solicitudes</h3>
            <p class="text-muted fs-13"><?= $solicitudes ?> pendientes</p>
        </a>
        <a href="<?= SITE_URL ?>admin/temporadas.php" class="card card-accent text-center">
            <span class="fs-36">📅</span>
            <h3>Temporadas</h3>
            <p class="text-muted fs-13">Crear y gestionar temporadas</p>
        </a>
        <a href="<?= SITE_URL ?>admin/carreras.php" class="card card-accent text-center">
            <span class="fs-36">🏁</span>
            <h3>Carreras</h3>
            <p class="text-muted fs-13">CRUD carreras y eventos</p>
        </a>
        <a href="<?= SITE_URL ?>admin/resultados.php" class="card card-accent text-center">
            <span class="fs-36">🏆</span>
            <h3>Resultados</h3>
            <p class="text-muted fs-13">Publicar resultados oficiales</p>
        </a>
        <a href="<?= SITE_URL ?>sanciones.php" class="card card-accent text-center">
            <span class="fs-36">⚠️</span>
            <h3>Sanciones</h3>
            <p class="text-muted fs-13">Gestionar penalizaciones</p>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
