<?php
// Cabecera (header) con menú dinámico según si el usuario está logueado y su rol
$_pagina_actual = basename($_SERVER['PHP_SELF'], '.php');
$titulo_final = 'LFS Competition';
if (isset($titulo_pagina) && $titulo_pagina !== '') {
    $titulo_final = $titulo_pagina;
}

$clase_boton_inscripcion = 'btn btn-gold btn-sm';
$clase_menu_inicio = '';
$clase_menu_calendario = '';
$clase_menu_clasificacion = '';
$clase_menu_resultados = '';
$clase_menu_pilotos = '';
$clase_menu_equipos = '';
$clase_menu_equipo = '';

switch ($_pagina_actual) {
    case 'index':
        $clase_menu_inicio = 'active';
        break;
    case 'calendario':
        $clase_menu_calendario = 'active';
        break;
    case 'clasificacion':
        $clase_menu_clasificacion = 'active';
        break;
    case 'resultados':
        $clase_menu_resultados = 'active';
        break;
    case 'pilotos':
        $clase_menu_pilotos = 'active';
        break;
    case 'equipos':
        $clase_menu_equipos = 'active';
        break;
    case 'inscripcion_equipo':
        $clase_menu_equipo = 'active';
        break;
    case 'inscripcion':
        $clase_boton_inscripcion .= ' active';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($titulo_final) ?></title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>imagenes/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=Rajdhani:wght@600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>css/estilos.css">
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <a href="<?= SITE_URL ?>index.php" class="brand">
      <img src="<?= SITE_URL ?>imagenes/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'" />
      <span>LFS<span class="text-accent">Competition</span></span>
    </a>

    <button type="button" id="nav-toggle" class="nav-toggle btn btn-admin btn-sm" aria-controls="nav-collapse" aria-expanded="false" aria-label="Menú">
      ☰
    </button>

    <div class="nav-collapse" id="nav-collapse">
      <div class="menu">
        <a href="<?= SITE_URL ?>index.php" class="<?= $clase_menu_inicio ?>">Inicio</a>
        <a href="<?= SITE_URL ?>calendario.php" class="<?= $clase_menu_calendario ?>">Calendario</a>
        <a href="<?= SITE_URL ?>clasificacion.php" class="<?= $clase_menu_clasificacion ?>">Clasificación</a>
        <a href="<?= SITE_URL ?>resultados.php" class="<?= $clase_menu_resultados ?>">Resultados</a>
        <a href="<?= SITE_URL ?>pilotos.php" class="<?= $clase_menu_pilotos ?>">Pilotos</a>
        <a href="<?= SITE_URL ?>equipos.php" class="<?= $clase_menu_equipos ?>">Equipos</a>
        <?php if (estaLogueado()) { ?>
          <a href="<?= SITE_URL ?>inscripcion_equipo.php" class="<?= $clase_menu_equipo ?>">🛡️ Equipo</a>
        <?php } ?>
      </div>

      <div class="nav-actions">
        <?php if (esAdmin()) { ?>
          <a href="<?= SITE_URL ?>admin/index.php" class="btn btn-admin btn-sm">⚙️ Panel</a>
          <a href="<?= SITE_URL ?>perfil.php" class="btn btn-accent btn-sm">👤 <?= e($_SESSION['nombre_usuario']) ?></a>
          <a href="<?= SITE_URL ?>logout.php" class="btn btn-danger btn-sm">🚪</a>

        <?php } elseif (estaLogueado()) { ?>
          <a href="<?= SITE_URL ?>perfil.php" class="btn btn-accent btn-sm">👤 <?= e($_SESSION['nombre_usuario']) ?></a>
          <a href="<?= SITE_URL ?>inscripcion.php" class="<?= $clase_boton_inscripcion ?>">🏎️</a>
          <a href="<?= SITE_URL ?>logout.php" class="btn btn-danger btn-sm">🚪</a>

        <?php } else { ?>
          <a href="<?= SITE_URL ?>login.php" class="btn btn-admin btn-sm nav-auth-login">Login</a>
          <a href="<?= SITE_URL ?>registro.php" class="btn btn-accent btn-sm nav-auth-registro">Registro</a>
        <?php } ?>
        
        <button id="theme-btn" onclick="toggleTheme()" class="btn btn-sm btn-theme">☀️</button>
      </div>
    </div>
  </div>
</nav>
