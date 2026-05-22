<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requiereLogin();
$usuario = usuarioActual($pdo);
$exito   = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = '';
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email no válido.';
    }

    // Comprobamos que el email no lo esté usando otra persona (excepto yo mismo)
    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $usuario['id']]);
        if ($stmt->fetch()) {
            $errores[] = 'Ese email ya está en uso por otro usuario.';
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET email = ? WHERE id = ?"
        );
        $stmt->execute([$email, $usuario['id']]);

        // Si el usuario escribió una nueva contraseña, la cambiamos (si no, se queda igual)
        $nueva_pass = '';
        if (isset($_POST['nueva_password'])) {
            $nueva_pass = $_POST['nueva_password'];
        }
        if (!empty($nueva_pass)) {
            if (strlen($nueva_pass) < 6) {
                $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            } else {
                $hash = password_hash($nueva_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET contraseña = ? WHERE id = ?");
                $stmt->execute([$hash, $usuario['id']]);
            }
        }

        if (empty($errores)) {
            $exito = 'Perfil actualizado correctamente.';
            $usuario = usuarioActual($pdo);
        }
    }
}

$titulo_pagina = 'Mi Perfil — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>👤 Mi Perfil</h1>
    <p>Gestiona tu información de piloto</p>
</div>

<div class="container">
    <div class="form-card">
        <?php if ($exito) { ?>
            <div class="msg msg-success"><?= e($exito) ?></div>
        <?php } ?>
        <?php foreach ($errores as $err) { ?>
            <div class="msg msg-error"><?= e($err) ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label>Nombre de usuario</label>
                <input type="text" value="<?= e($usuario['nombre_usuario']) ?>" disabled class="opacity-50">
                <small class="text-muted fs-11">El nombre de usuario no se puede cambiar</small>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= e($usuario['email']) ?>" required>
            </div>

            <hr class="hr-line">

            <div class="form-group">
                <label>Nueva contraseña <span class="text-muted no-transform">(dejar vacío para mantener)</span></label>
                <input type="password" name="nueva_password" placeholder="Mínimo 6 caracteres" minlength="6">
            </div>

            <button type="submit" class="btn-submit">Guardar cambios ✅</button>
        </form>

        <?php
        $estadoSolicitud = '';
        if (isset($usuario['estado_solicitud']) && $usuario['estado_solicitud'] !== '') {
            $estadoSolicitud = $usuario['estado_solicitud'];
        }
        ?>

        <?php if ($usuario['equipo']) { ?>
            <?php
            $stmt = $pdo->prepare("SELECT nombre FROM equipos WHERE id = ?");
            $stmt->execute([$usuario['equipo']]);
            $equipo = $stmt->fetch();
            $equipoNombre = 'Desconocido';
            if ($equipo && isset($equipo['nombre']) && $equipo['nombre'] !== '') {
                $equipoNombre = $equipo['nombre'];
            }
            ?>
            <div class="msg msg-info mt-2 text-center">
                🛡️ Equipo actual: <strong><?= e($equipoNombre) ?></strong>
                <div class="mt-1">
                    <a href="<?= SITE_URL ?>inscripcion_equipo.php" class="btn btn-accent btn-sm">Gestionar equipo</a>
                </div>
            </div>
        <?php } elseif ($estadoSolicitud === 'invitado') { ?>
            <div class="msg msg-info mt-2 text-center">
                📩 Tienes una invitación de equipo pendiente.
                <div class="mt-1">
                    <a href="<?= SITE_URL ?>inscripcion_equipo.php" class="btn btn-success btn-sm">Ver invitación</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="mt-2 text-center">
                <p class="text-muted fs-13">Actualmente no perteneces a ningún equipo.</p>
                <a href="<?= SITE_URL ?>inscripcion_equipo.php" class="btn btn-accent btn-sm mt-1">🛡️ Ir a mi sección de equipo</a>
            </div>
        <?php } ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
