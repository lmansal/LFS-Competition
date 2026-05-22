<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$proximaCarrera = proximaCarrera($pdo);
$totalPilotos = contarRegistros($pdo, 'usuarios', "rango_piloto != 'admin'");
$totalEquipos = contarRegistros($pdo, 'equipos');

$titulo_pagina = 'LFS Competition — Competiciones de Live for Speed';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Sección principal (hero) -->
<section class="container">
    <div class="hero-split">
        <div class="hero-content">
            <span class="badge badge-accent mb-1">Live for Speed Competition</span>
            <h1>Domina la<br><span class="text-accent">Pista.</span></h1>
            <p>Únete a la comunidad de sim racing más competitiva. Organiza tus equipos, compite en grandes premios y escala hasta la cima de la clasificación.</p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>registro.php" class="btn btn-primary">Empezar ahora</a>
                <a href="<?= SITE_URL ?>calendario.php" class="btn">Ver Calendario</a>
            </div>
        </div>

        <div class="hero-sidebar">
            <?php if ($proximaCarrera) { ?>
                <div class="next-race-card">
                    <div class="mb-20">
                        <span class="badge badge-gold mb-1">Próximo Evento</span>
                        <h2 class="next-race-title"><?= e($proximaCarrera['nombre']) ?></h2>
                        <div class="next-race-meta">
                            <div class="flex items-center gap-5">
                                <span class="fs-14">📅</span>
                                <span class="text-accent next-race-date"><?= fechaCorta($proximaCarrera['fecha']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="countdown countdown--start my-20" id="countdown-hero"></div>
                    
                    <div class="card-footer-row">
                        <span class="text-muted fs-12">🏆 <?= e($proximaCarrera['temporada_nombre']) ?></span>
                        <a href="<?= SITE_URL ?>inscripcion.php" class="link-accent-strong">Inscribirse →</a>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        iniciarCountdown('<?= $proximaCarrera['fecha'] ?> 23:59:59', 'countdown-hero');
                    });
                </script>
            <?php } else { ?>
                <div class="card text-center p-40">
                    <div class="empty-icon mb-10">🏁</div>
                    <h3 class="mb-1">Sin carreras a la vista</h3>
                    <p class="text-muted fs-13">Estamos preparando la próxima temporada. ¡Mantente atento!</p>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<!-- Barra con números rápidos -->
<section class="container">
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-value"><?= $totalPilotos ?></div>
            <div class="stat-label">Pilotos</div>
        </div>
        <div class="stat-item">
            <div class="stat-value"><?= $totalEquipos ?></div>
            <div class="stat-label">Equipos</div>
        </div>
        <div class="stat-item">
            <?php $totalCarreras = contarRegistros($pdo, 'carreras'); ?>
            <div class="stat-value"><?= $totalCarreras ?></div>
            <div class="stat-label">Carreras</div>
        </div>
    </div>
</section>

<!-- Tarjetas de acceso rápido -->
<section class="container mb-3">
    <h2 class="text-center mb-3">Acceso Rápido</h2>
    <div class="quick-grid">
        <a href="<?= SITE_URL ?>clasificacion.php" class="quick-card">
            <i>📊</i>
            <h3>Clasificación</h3>
            <p>Consulta el ranking global y los puntos de la temporada.</p>
        </a>
        <a href="<?= SITE_URL ?>calendario.php" class="quick-card">
            <i>📅</i>
            <h3>Calendario</h3>
            <p>No te pierdas ninguna cita. Horarios y circuitos confirmados.</p>
        </a>
        <a href="<?= SITE_URL ?>resultados.php" class="quick-card">
            <i>🏁</i>
            <h3>Resultados</h3>
            <p>Repasa lo ocurrido en pista y las sanciones aplicadas.</p>
        </a>
    </div>
</section>

<!-- Sección de comunidad (enlaces a redes) -->
<section class="container mb-3">
    <h2 class="text-center mb-1">Únete a la Comunidad</h2>
    <p class="text-center text-muted mb-3">Conecta con otros pilotos en nuestras plataformas</p>
    
    <div class="community-grid">
        <a href="#" class="community-item item-discord">
            <i>💬</i>
            <span>Discord</span>
        </a>
        <a href="#" class="community-item item-youtube">
            <i>📺</i>
            <span>YouTube</span>
        </a>
        <a href="#" class="community-item item-instagram">
            <i>📸</i>
            <span>Instagram</span>
        </a>
        <a href="#" class="community-item item-twitch">
            <i>🎬</i>
            <span>Twitch</span>
        </a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
