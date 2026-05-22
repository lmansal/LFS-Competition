<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (estaLogueado()) {
    header('Location: ' . SITE_URL . 'perfil.php');
    exit;
}

$errores = [];
$exito   = false;

$old_nombre_usuario = '';
$old_email = '';
if (isset($_POST['nombre_usuario'])) {
    $old_nombre_usuario = $_POST['nombre_usuario'];
}
if (isset($_POST['email'])) {
    $old_email = $_POST['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [];
    $datos['nombre_usuario'] = '';
    if (isset($_POST['nombre_usuario'])) {
        $datos['nombre_usuario'] = trim($_POST['nombre_usuario']);
    }
    $datos['email'] = '';
    if (isset($_POST['email'])) {
        $datos['email'] = trim($_POST['email']);
    }
    $datos['password'] = '';
    if (isset($_POST['password'])) {
        $datos['password'] = $_POST['password'];
    }
    $datos['nombre_real'] = '';
    if (isset($_POST['nombre_real'])) {
        $datos['nombre_real'] = trim($_POST['nombre_real']);
    }
    $datos['pais'] = '';
    if (isset($_POST['pais'])) {
        $datos['pais'] = $_POST['pais'];
    }
    $errores = registrarUsuario($pdo, $datos);
    if (empty($errores)) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro — LFS Competition</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>imagenes/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Rajdhani:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>css/estilos.css">
</head>
<body>

<div class="bg-liquid"></div>

<div class="auth-shell">
    <div class="auth-box-lg">
        <div class="glass-card text-left">
            <div class="text-center mb-28">
                <h1 class="auth-title">Crear Cuenta</h1>
                <p class="text-muted fs-14">Únete a la competición de Live for Speed</p>
            </div>

            <?php foreach ($errores as $err) { ?>
                <div class="msg msg-error"><?= e($err) ?></div>
            <?php } ?>

            <form method="POST">
                <div class="form-group">
                    <label for="registro-nombre-usuario" class="sr-only">Nombre de usuario *</label>
                    <input type="text" id="registro-nombre-usuario" name="nombre_usuario" placeholder="Tu nick de piloto" required minlength="3" value="<?= e($old_nombre_usuario) ?>">
                </div>
                <div class="form-group">
                    <label for="registro-email" class="sr-only">Email *</label>
                    <input type="email" id="registro-email" name="email" placeholder="tu@email.com" required value="<?= e($old_email) ?>">
                </div>
                <div class="form-group">
                    <label for="registro-password" class="sr-only">Contraseña *</label>
                    <input type="password" id="registro-password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <button type="submit" class="btn-submit">Crear cuenta 🏁</button>
            </form>

            <p class="text-center mt-20 fs-13">
                <span class="text-muted">¿Ya tienes cuenta?</span>
                <a href="<?= SITE_URL ?>login.php" class="text-accent fw-700"> Inicia sesión</a>
            </p>
            <p class="text-center mt-10">
                <a href="<?= SITE_URL ?>index.php" class="text-muted fs-13">← Volver al inicio</a>
            </p>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>js/tema.js"></script>
</body>
</html>
