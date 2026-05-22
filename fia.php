<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$titulo_pagina = 'Staff & Comisarios — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>👨‍⚖️ Staff & Comisarios</h1>
    <p>Equipo organizador de la competición</p>
</div>

<div class="container pb-40">
    <div class="grid-3">
        <div class="card text-center">
            <div class="fs-48 mb-12">👑</div>
            <h3>Director de Competición</h3>
            <p class="text-accent fw-700">Administrador</p>
            <p class="text-muted fs-13">Responsable general de la organización y gestión de las temporadas.</p>
        </div>
        <div class="card text-center">
            <div class="fs-48 mb-12">⚖️</div>
            <h3>Comisario Principal</h3>
            <p class="text-accent fw-700">Por designar</p>
            <p class="text-muted fs-13">Encargado de revisar incidentes y aplicar sanciones según el reglamento.</p>
        </div>
        <div class="card text-center">
            <div class="fs-48 mb-12">📋</div>
            <h3>Director de Carrera</h3>
            <p class="text-accent fw-700">Por designar</p>
            <p class="text-muted fs-13">Supervisa el desarrollo de cada evento y gestiona los tiempos oficiales.</p>
        </div>
    </div>

    <div class="card mt-3">
        <h2 class="text-center text-accent mb-2">📧 Contacto del Staff</h2>
        <p class="text-center text-muted fs-14">
            Para consultas sobre incidentes, sanciones o cuestiones técnicas, contacta con nosotros a través del
            <a href="<?= SITE_URL ?>contacto.php" class="text-accent fw-700">formulario de contacto</a>.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
