<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$usuario = null;
if (estaLogueado()) {
    $usuario = usuarioActual($pdo);
}
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí iría la lógica real de envío (por ejemplo mandar email o guardar en BD)
    $enviado = true;
}

$titulo_pagina = 'Contacto — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>📧 Contacto</h1>
    <p>Envíanos tus dudas, sugerencias o reportes</p>
</div>

<div class="container pb-40">
    <div class="form-card">
        <?php if ($enviado) { ?>
            <div class="msg msg-success">✅ Mensaje enviado correctamente. Te responderemos lo antes posible.</div>
        <?php } ?>

        <?php
        $valorNombre = '';
        $valorEmail = '';
        if ($usuario) {
            $valorNombre = $usuario['nombre_usuario'];
            $valorEmail = $usuario['email'];
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required value="<?= e($valorNombre) ?>" placeholder="Tu nombre">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= e($valorEmail) ?>" placeholder="tu@email.com">
            </div>
            <div class="form-group">
                <label>Asunto</label>
                <select name="asunto">
                    <option value="general">Consulta general</option>
                    <option value="incidente">Reporte de incidente</option>
                    <option value="tecnico">Problema técnico</option>
                    <option value="sugerencia">Sugerencia</option>
                </select>
            </div>
            <div class="form-group">
                <label>Mensaje</label>
                <textarea name="mensaje" required placeholder="Escribe tu mensaje aquí..."></textarea>
            </div>
            <button type="submit" class="btn-submit">Enviar mensaje 📩</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
