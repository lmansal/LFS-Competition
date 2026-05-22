<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requiereAdmin();
$msg = '';

// Cogemos la carrera seleccionada (puede venir por GET o por POST)
$carrera_id = 0;
if (isset($_GET['carrera']) && $_GET['carrera'] !== '') {
    $carrera_id = (int)$_GET['carrera'];
} elseif (isset($_POST['carrera_id']) && $_POST['carrera_id'] !== '') {
    $carrera_id = (int)$_POST['carrera_id'];
}

// Si envían el formulario, guardamos los resultados de esa carrera
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resultados'])) {
    $carrera_id = (int)$_POST['carrera_id'];
    $resultados = $_POST['resultados'];

    // Antes de insertar, borramos resultados anteriores de esa misma carrera
    $pdo->prepare("DELETE FROM resultados WHERE carrera = ?")->execute([$carrera_id]);

    // Preparamos el INSERT una vez y lo reutilizamos en el bucle
    $stmt = $pdo->prepare(
        "INSERT INTO resultados (posicion, tiempo_total, puntos, carrera, usuario)
         VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($resultados as $uid => $r) {
        if (empty($r['posicion'])) {
            continue;
        }
        $uid = (int)$uid;
        $pos = (int)$r['posicion'];
        $puntos = 0;
        if (!empty($r['puntos'])) {
            $puntos = (int)$r['puntos'];
        }
        
        // El admin mete el tiempo como texto y nosotros lo pasamos a milisegundos (número)
        $tiempo_total = 0;
        if (!empty($r['tiempo_total'])) {
            $tiempo_total = tiempoAMilisegundos($r['tiempo_total']);
        }

        $stmt->execute([$pos, $tiempo_total, $puntos, $carrera_id, $uid]);
    }

    // Marcamos la carrera como terminada cuando publicamos resultados
    $pdo->prepare("UPDATE carreras SET estado = 'terminada' WHERE id = ?")->execute([$carrera_id]);
    $msg = 'success:Resultados publicados correctamente. 🏆';
}

// Listado de carreras para el desplegable (mostramos también la temporada)
$carreras = $pdo->query(
    "SELECT c.*, t.nombre AS temporada_nombre FROM carreras c JOIN temporadas t ON c.temporada = t.id ORDER BY c.fecha DESC"
)->fetchAll();

// Si hay carrera seleccionada, sacamos los inscritos para poder rellenar la tabla
$inscritos = [];
$carreraActual = null;
if ($carrera_id) {
    $stmt = $pdo->prepare("SELECT * FROM carreras WHERE id = ?");
    $stmt->execute([$carrera_id]);
    $carreraActual = $stmt->fetch();

    // Traemos inscritos y, si ya había resultados guardados, los mostramos para editar fácilmente
    $stmt = $pdo->prepare(
        "SELECT u.id, u.nombre_usuario, e.nombre AS equipo_nombre,
                r.posicion, r.tiempo_total, r.puntos
         FROM (
            SELECT i.usuario AS usuario
            FROM inscripciones i
            WHERE i.carrera = ? AND i.estado = 'inscrito'
            UNION
            SELECT rr.usuario AS usuario
            FROM resultados rr
            WHERE rr.carrera = ?
         ) x
         JOIN usuarios u ON x.usuario = u.id
         LEFT JOIN equipos e ON u.equipo = e.id
         LEFT JOIN resultados r ON r.carrera = ? AND r.usuario = u.id
         ORDER BY CASE WHEN r.posicion IS NULL THEN 1 ELSE 0 END, r.posicion ASC, u.nombre_usuario"
    );
    $stmt->execute([$carrera_id, $carrera_id, $carrera_id]);
    $inscritos = $stmt->fetchAll();
}

$titulo_pagina = 'Admin: Resultados — LFS Competition';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container text-center py-20">
        <h1>🏆 Publicar Resultados</h1>
        <p><a href="<?= SITE_URL ?>admin/index.php" class="text-accent">← Volver al Panel Admin</a></p>
    </div>
</div>

<div class="container pb-40">
    <?php if ($msg) { ?>
        <?php [$tipo, $texto] = explode(':', $msg, 2); ?>
        <div class="msg msg-<?= $tipo ?>"><?= e($texto) ?></div>
    <?php } ?>

    <!-- Selector de carrera (elegimos qué carrera vamos a editar/publicar) -->
    <div class="card mb-3">
        <form method="GET" class="flex gap-12 items-end flex-wrap">
            <div class="form-group flex-1 min-w-200">
                <label>Seleccionar carrera</label>
                <select name="carrera" onchange="this.form.submit()">
                    <option value="">— Elige una carrera —</option>
                    <?php foreach ($carreras as $c) { ?>
                        <?php
                        $selected = '';
                        if ($carrera_id == $c['id']) {
                            $selected = 'selected';
                        }
                        $iconoEstado = '⏳';
                        if ($c['estado'] === 'terminada') {
                            $iconoEstado = '✅';
                        }
                        ?>
                        <option value="<?= $c['id'] ?>" <?= $selected ?>>
                            <?= e($c['nombre']) ?> (<?= e($c['temporada_nombre']) ?>) <?= $iconoEstado ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </form>
    </div>

    <?php if ($carreraActual && !empty($inscritos)) { ?>
        <div class="card card-accent mb-2">
            <h2><?= e($carreraActual['nombre']) ?></h2>
            <p class="text-muted"><?= fechaHora($carreraActual['fecha']) ?> · <?= count($inscritos) ?> pilotos inscritos</p>
        </div>

        <form method="POST">
            <input type="hidden" name="carrera_id" value="<?= $carrera_id ?>">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Piloto</th>
                            <th>Equipo</th>
                            <th class="w-70">Pos</th>
                            <th>Tiempo (seg)</th>
                            <th class="w-60">Puntos</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inscritos as $p) { ?>
                            <?php
                            $equipoNombre = '—';
                            if (isset($p['equipo_nombre']) && $p['equipo_nombre'] !== '') {
                                $equipoNombre = $p['equipo_nombre'];
                            }
                            $valorPosicion = '';
                            if (isset($p['posicion']) && $p['posicion'] !== null && $p['posicion'] !== '') {
                                $valorPosicion = $p['posicion'];
                            }
                            $valorTiempo = '';
                            if (!empty($p['tiempo_total'])) {
                                $valorTiempo = formatoTiempo((int)$p['tiempo_total']);
                            }
                            $valorPuntos = '';
                            if (isset($p['puntos']) && $p['puntos'] !== null && $p['puntos'] !== '') {
                                $valorPuntos = $p['puntos'];
                            }
                            ?>
                            <tr>
                                <td><strong><?= e($p['nombre_usuario']) ?></strong></td>
                                <td class="text-success"><?= e($equipoNombre) ?></td>
                                <td><input type="number" name="resultados[<?= $p['id'] ?>][posicion]" min="1" value="<?= $valorPosicion ?>" class="w-60"></td>
                                <td>
                                    <input type="text" name="resultados[<?= $p['id'] ?>][tiempo_total]" 
                                           value="<?= $valorTiempo ?>" 
                                           placeholder="HH:MM:SS.mmm" 
                                           class="w-120"
                                           pattern="^(\d+:)?\d{2}:\d{2}\.\d{3}$"
                                           title="Formato: HH:MM:SS.mmm">
                                </td>
                                <td><input type="number" name="resultados[<?= $p['id'] ?>][puntos]" min="0" value="<?= $valorPuntos ?>" class="w-60"></td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="abrirModalSancion(<?= $p['id'] ?>, '<?= e($p['nombre_usuario']) ?>')">⚠️ Sanción</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-2">
                <button type="submit" class="btn btn-gold btn-lg">🏆 Publicar resultados</button>
            </div>
        </form>
    <?php } elseif ($carrera_id) { ?>
        <div class="text-center p-40">
            <span class="fs-48">📭</span>
            <p class="text-muted mt-2">No hay pilotos inscritos en esta carrera.</p>
        </div>
    <?php } ?>
</div>

<!-- Modal de sanciones (se abre desde el botón ⚠️) -->
<div id="modal-sancion" class="modal-overlay">
    <div class="modal-box">
        <button class="modal-close" onclick="cerrarModalSancion()">×</button>
        <h2 id="modal-titulo">⚠️ Añadir Sanción Oficial</h2>
        <p id="modal-subtitulo" class="text-muted mb-3"></p>
        
        <form id="form-sancion-modal">
            <input type="hidden" name="usuario" id="modal-usuario-id">
            <input type="hidden" name="carrera" value="<?= $carrera_id ?>">
            
            <div class="grid-2">
                 <div class="form-group">
                     <label>Tipo de Sanción *</label>
                     <select name="tipo" required>
                         <option value="Aviso">Aviso</option>
                         <option value="Penalización de tiempo">Penalización de tiempo</option>
                         <option value="Penalización de posiciones">Penalización de posiciones</option>
                         <option value="DSQ">DSQ (Descalificación)</option>
                         <option value="Suspensión">Suspensión</option>
                     </select>
                 </div>
             </div>
            
            <div class="form-group">
                <label>Descripción / Motivo</label>
                <textarea name="descripcion" placeholder="Ej: Provocar colisión múltiple en la curva 1..." required class="h-100"></textarea>
            </div>
            
            <div id="modal-msg"></div>
            
            <div class="flex gap-10 mt-3">
                <button type="submit" class="btn-submit m-0 flex-2">Guardar Sanción ⚠️</button>
                <button type="button" class="btn btn-accent flex-1 flex-center" onclick="cerrarModalSancion()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalSancion(uid, nombre) {
    document.getElementById('modal-usuario-id').value = uid;
    document.getElementById('modal-subtitulo').innerText = 'Piloto: ' + nombre;
    document.getElementById('modal-sancion').classList.add('show');
    document.getElementById('modal-msg').innerHTML = '';
}

function cerrarModalSancion() {
    document.getElementById('modal-sancion').classList.remove('show');
}

document.getElementById('form-sancion-modal').onsubmit = function(e) {
    e.preventDefault();
    var form = this;
    var btn = form.querySelector('button[type="submit"]');
    var msgDiv = document.getElementById('modal-msg');
    
    btn.disabled = true;
    btn.innerText = 'Guardando...';
    
    var formData = new FormData(form);
    
    // Enviamos el formulario por AJAX (fetch) para guardar la sanción sin recargar la página
    fetch('<?= SITE_URL ?>admin/sanciones_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(function (res) {
        return res.json();
    })
    .then(function (data) {
        if (data.success) {
            msgDiv.innerHTML = '<div class="msg msg-success">Sanción guardada correctamente.</div>';
            setTimeout(cerrarModalSancion, 1500);
            form.reset();
        } else {
            msgDiv.innerHTML = '<div class="msg msg-error">' + data.error + '</div>';
        }

        btn.disabled = false;
        btn.innerText = 'Guardar Sanción ⚠️';
    })
    .catch(function () {
        msgDiv.innerHTML = '<div class="msg msg-error">Error al conectar con el servidor.</div>';
        btn.disabled = false;
        btn.innerText = 'Guardar Sanción ⚠️';
    });
};
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
