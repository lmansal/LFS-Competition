<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requiereLogin();
$usuario = usuarioActual($pdo);
$msg = '';

// Acciones del piloto: aceptar/rechazar invitación o abandonar su equipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_piloto'])) {
    $accion = $_POST['accion_piloto'];
    if ($accion === 'aceptar' && $usuario['estado_solicitud'] === 'invitado') {
        $stmt = $pdo->prepare("UPDATE usuarios SET equipo = solicitud_equipo, solicitud_equipo = NULL, estado_solicitud = 'aprobada' WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $msg = 'success:¡Te has unido al equipo correctamente! 🛡️';
        $usuario = usuarioActual($pdo);
    } elseif ($accion === 'rechazar') {
        $stmt = $pdo->prepare("UPDATE usuarios SET solicitud_equipo = NULL, estado_solicitud = 'rechazada' WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $msg = 'success:Invitación rechazada.';
        $usuario = usuarioActual($pdo);
    } elseif ($accion === 'abandonar' && $usuario['equipo']) {
        $stmt = $pdo->prepare("UPDATE usuarios SET equipo = NULL, rango_piloto = 'piloto', solicitud_equipo = NULL, estado_solicitud = NULL WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        $msg = 'success:Has abandonado el equipo correctamente.';
        $usuario = usuarioActual($pdo);
    }
}

$rango_piloto_usuario = '';
if (isset($usuario['rango_piloto'])) {
    $rango_piloto_usuario = $usuario['rango_piloto'];
}
$estado_solicitud_usuario = '';
if (isset($usuario['estado_solicitud'])) {
    $estado_solicitud_usuario = $usuario['estado_solicitud'];
}

// Acciones del jefe de equipo: invitar, expulsar o cambiar rangos dentro del equipo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_jefe'])) {
    if ($rango_piloto_usuario !== 'jefe_equipo' || !$usuario['equipo']) {
        $msg = 'error:No tienes permisos para invitar pilotos.';
    } else {
        $accion = $_POST['accion_jefe'];
        
        if ($accion === 'invitar') {
            $piloto_id = (int)$_POST['piloto_id'];
            $stmt = $pdo->prepare("UPDATE usuarios SET solicitud_equipo = ?, estado_solicitud = 'invitado' WHERE id = ? AND equipo IS NULL");
            $stmt->execute([$usuario['equipo'], $piloto_id]);
            $msg = 'success:Invitación enviada al piloto. 🛡️';
        } elseif ($accion === 'expulsar') {
            $piloto_id = (int)$_POST['piloto_id'];
            // Por seguridad: el jefe no puede expulsarse a sí mismo
            if ($piloto_id != $usuario['id']) {
                $stmt = $pdo->prepare("UPDATE usuarios SET equipo = NULL, rango_piloto = 'piloto' WHERE id = ? AND equipo = ?");
                $stmt->execute([$piloto_id, $usuario['equipo']]);
                $msg = 'success:Piloto expulsado del equipo.';
            }
        } elseif ($accion === 'cambiar_rango') {
            $piloto_id = (int)$_POST['piloto_id'];
            $nuevo_rango = $_POST['nuevo_rango'];
            if ($piloto_id != $usuario['id'] && in_array($nuevo_rango, ['piloto', 'jefe_equipo'])) {
                $stmt = $pdo->prepare("UPDATE usuarios SET rango_piloto = ? WHERE id = ? AND equipo = ?");
                $stmt->execute([$nuevo_rango, $piloto_id, $usuario['equipo']]);
                $msg = 'success:Rango actualizado.';
            }
        }
    }
}

$equipos = $pdo->query("SELECT * FROM equipos ORDER BY nombre ASC")->fetchAll();
$pilotos_sin_equipo = $pdo->query("SELECT id, nombre_usuario FROM usuarios WHERE equipo IS NULL AND rango_piloto != 'admin' ORDER BY nombre_usuario ASC")->fetchAll();

$titulo_pagina = 'Mi Equipo — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>🛡️ Mi Equipo</h1>
    <p>Gestiona tu afiliación a un equipo</p>
</div>

<div class="container max-w-800 pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <!-- Vista: Jefe de Equipo -->
    <?php if ($rango_piloto_usuario === 'jefe_equipo' && $usuario['equipo']) { ?>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM equipos WHERE id = ?");
        $stmt->execute([$usuario['equipo']]);
        $mi_equipo = $stmt->fetch();
        ?>
        <div class="card card-accent mb-3 text-center">
            <h2 class="text-success">🛡️ Eres Jefe de <?= e($mi_equipo['nombre']) ?></h2>
            <p class="text-muted">Como jefe de equipo, puedes invitar a pilotos que no tengan equipo.</p>
        </div>

        <div class="form-card mb-3">
            <h3>Invitar un piloto</h3>
            <form method="POST">
                <input type="hidden" name="accion_jefe" value="invitar">
                <div class="form-group">
                    <label>Seleccionar piloto sin equipo</label>
                    <select name="piloto_id" required>
                        <option value="">— Elige —</option>
                        <?php foreach ($pilotos_sin_equipo as $p) { ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['nombre_usuario']) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Enviar invitación 📩</button>
            </form>
        </div>

        <div class="card">
            <h3>Miembros del equipo</h3>
            <?php
            $stmt = $pdo->prepare("SELECT id, nombre_usuario, rango_piloto FROM usuarios WHERE equipo = ? ORDER BY nombre_usuario ASC");
            $stmt->execute([$usuario['equipo']]);
            $miembros = $stmt->fetchAll();
            ?>
            <div class="table-wrapper mt-10">
                <table>
                    <thead>
                        <tr>
                            <th>Piloto</th>
                            <th>Rango</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($miembros as $m) { ?>
                            <tr>
                                <td><strong><?= e($m['nombre_usuario']) ?></strong></td>
                                <td><span class="badge"><?= e($m['rango_piloto']) ?></span></td>
                                <td class="text-right">
                                    <?php if ($m['id'] != $usuario['id']) { ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="accion_jefe" value="cambiar_rango">
                                            <input type="hidden" name="piloto_id" value="<?= $m['id'] ?>">
                                            <select name="nuevo_rango" onchange="this.form.submit()" class="select-xs">
                                                <?php
                                                $selectedPiloto = '';
                                                if ($m['rango_piloto'] === 'piloto') {
                                                    $selectedPiloto = 'selected';
                                                }
                                                $selectedJefe = '';
                                                if ($m['rango_piloto'] === 'jefe_equipo') {
                                                    $selectedJefe = 'selected';
                                                }
                                                ?>
                                                <option value="piloto" <?= $selectedPiloto ?>>Piloto</option>
                                                <option value="jefe_equipo" <?= $selectedJefe ?>>Jefe</option>
                                            </select>
                                        </form>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Expulsar a este piloto?')">
                                            <input type="hidden" name="accion_jefe" value="expulsar">
                                            <input type="hidden" name="piloto_id" value="<?= $m['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-xs">🗑️</button>
                                        </form>
                                    <?php } else { ?>
                                        <span class="text-muted fs-11">(Tú)</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Vista: Piloto con invitación pendiente -->
    <?php } elseif ($estado_solicitud_usuario === 'invitado') { ?>
        <?php
        $stmt = $pdo->prepare("SELECT nombre FROM equipos WHERE id = ?");
        $stmt->execute([$usuario['solicitud_equipo']]);
        $eqSol = $stmt->fetch();
        $nombreEquipoInvita = '';
        if ($eqSol && isset($eqSol['nombre']) && $eqSol['nombre'] !== '') {
            $nombreEquipoInvita = $eqSol['nombre'];
        }
        ?>
        <div class="card text-center card-accent">
            <div class="fs-48 mb-12">📩</div>
            <h2>¡Tienes una invitación!</h2>
            <p class="text-muted">El equipo <strong class="text-success"><?= e($nombreEquipoInvita) ?></strong> te ha invitado a unirte.</p>
            <div class="flex justify-center gap-10 mt-20">
                <form method="POST">
                    <input type="hidden" name="accion_piloto" value="aceptar">
                    <button type="submit" class="btn btn-success">✅ Aceptar</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="accion_piloto" value="rechazar">
                    <button type="submit" class="btn btn-danger">❌ Rechazar</button>
                </form>
            </div>
        </div>

    <!-- Vista: Piloto que ya está en un equipo -->
    <?php } elseif ($usuario['equipo']) { ?>
        <?php
        $stmt = $pdo->prepare("SELECT * FROM equipos WHERE id = ?");
        $stmt->execute([$usuario['equipo']]);
        $equipo = $stmt->fetch();
        ?>
        <div class="card card-accent text-center">
            <div class="fs-48 mb-12">🛡️</div>
            <h2 class="text-success"><?= e($equipo['nombre']) ?></h2>
            <span class="badge badge-success mt-2">Miembro activo</span>
            <p class="text-muted mt-2 fs-13">Tu rango en el equipo es: <strong><?= e($usuario['rango_piloto']) ?></strong></p>
            
            <form method="POST" class="mt-3" onsubmit="return confirm('¿Seguro que quieres abandonar el equipo? Esta acción no se puede deshacer.')">
                <input type="hidden" name="accion_piloto" value="abandonar">
                <button type="submit" class="btn btn-danger btn-sm">🚪 Abandonar Equipo</button>
            </form>
        </div>

    <!-- Vista: Piloto sin equipo y sin invitación -->
    <?php } else { ?>
        <div class="card text-center p-40">
            <div class="fs-48 mb-12">🔍</div>
            <h2>Sin equipo</h2>
            <p class="text-muted">Actualmente no perteneces a ningún equipo ni tienes invitaciones pendientes.</p>
            <p class="text-muted fs-13">Espera a que un jefe de equipo te envíe una invitación o habla con ellos.</p>
        </div>
    <?php } ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
