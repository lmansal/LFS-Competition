<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Si ya hay sesión iniciada, mandamos al usuario a su zona (admin o portada)
if (estaLogueado()) {
    $destino = SITE_URL . 'index.php';
    if (esAdmin()) {
        $destino = SITE_URL . 'admin/index.php';
    }
    header('Location: ' . $destino);
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = '';
    if (isset($_POST['usuario'])) {
        $usuario = trim($_POST['usuario']);
    }
    $password = '';
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    if (loginUsuario($pdo, $usuario, $password)) {
        $destino = SITE_URL . 'index.php';
        if (esAdmin()) {
            $destino = SITE_URL . 'admin/index.php';
        }
        header('Location: ' . $destino);
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login — LFS Competition</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>imagenes/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Rajdhani:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>css/estilos.css">
</head>
<body>

<div class="bg-liquid"></div>

<div class="auth-shell">
    <div class="auth-box">
        <div class="glass-card">
            <img src="<?= SITE_URL ?>imagenes/logo.png" alt="Logo" class="auth-logo" onerror="this.style.display='none'">
            <h1 class="auth-title">Iniciar Sesión</h1>
            <p class="text-muted auth-subtitle">Accede a tu cuenta de piloto o administrador</p>

            <?php if ($error) { ?>
                <div class="msg msg-error"><?= e($error) ?></div>
            <?php } ?>

            <form method="POST">
                <div class="form-group">
                    <label for="login-usuario" class="sr-only">Usuario o Email</label>
                    <input type="text" id="login-usuario" name="usuario" placeholder="Tu nick o email" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="login-password" class="sr-only">Contraseña</label>
                    <input type="password" id="login-password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-submit mt-10">Entrar al Paddock 🏁</button>
            </form>

            <p class="mt-3 fs-13">
                <span class="text-muted">¿No tienes cuenta?</span>
                <a href="<?= SITE_URL ?>registro.php" class="text-accent fw-700"> Regístrate aquí</a>
            </p>
            <a href="<?= SITE_URL ?>index.php" class="text-muted d-inline-block mt-12 fs-13">← Volver al inicio</a>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>js/tema.js"></script>
</body>
</html>
