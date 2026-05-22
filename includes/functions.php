<?php
// ============================================================
// Funciones auxiliares
// ============================================================

/**
 * Escapa un texto antes de imprimirlo en HTML (para evitar ataques XSS)
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Devuelve la bandera (emoji) según el país
 */
function banderaPais(string $pais): string {
    $banderas = [
        'España' => '🇪🇸', 'Argentina' => '🇦🇷', 'México' => '🇲🇽',
        'Colombia' => '🇨🇴', 'Chile' => '🇨🇱', 'Perú' => '🇵🇪',
        'Brasil'=> '🇧🇷', 'Uruguay' => '🇺🇾', 'Venezuela' => '🇻🇪',
        'Ecuador' => '🇪🇨', 'Bolivia' => '🇧🇴', 'Paraguay' => '🇵🇾',
        'Portugal' => '🇵🇹', 'Italia' => '🇮🇹', 'Alemania' => '🇩🇪',
        'Francia' => '🇫🇷', 'Reino Unido' => '🇬🇧',
    ];
    if (isset($banderas[$pais])) {
        return $banderas[$pais];
    }
    return '🏁';
}

/**
 * Formatea una fecha (ej: "12 ABR 2026")
 */
function fechaCorta(string $fecha): string {
    $meses = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
    $ts = strtotime($fecha);
    if ($ts === false) {
        return $fecha;
    }
    return date('d', $ts) . ' ' . $meses[(int)date('m', $ts) - 1] . ' ' . date('Y', $ts);
}

/**
 * Formatea fecha y hora en formato corto (ej: "12 ABR 2026 — 20:00h")
 */
function fechaHora(string $fecha): string {
    $ts = strtotime($fecha);
    if ($ts === false) {
        return $fecha;
    }
    $hora = date('H:i', $ts);
    $tieneHoraEnTexto = strpos($fecha, ':') !== false;
    if (!$tieneHoraEnTexto) {
        return fechaCorta($fecha);
    }
    return fechaCorta($fecha) . ' — ' . $hora . 'h';
}

/**
 * Según la posición, devuelve una clase CSS para colorear el podio
 */
function clasePosicion(int $pos): string {
    switch ($pos) {
        case 1:
            return 'rank-gold';
        case 2:
            return 'rank-silver';
        case 3:
            return 'rank-bronze';
        default:
            return '';
    }
}

/**
 * Según la posición, devuelve la medalla (emoji)
 */
function medallaPosicion(int $pos): string {
    switch ($pos) {
        case 1:
            return '🥇';
        case 2:
            return '🥈';
        case 3:
            return '🥉';
        default:
            return '';
    }
}

/**
 * Busca la próxima carrera pendiente
 */
function proximaCarrera(PDO $pdo): ?array {
    $stmt = $pdo->query(
        "SELECT c.*, t.nombre AS temporada_nombre
         FROM carreras c
         JOIN temporadas t ON c.temporada = t.id
         WHERE c.estado = 'pendiente' AND c.fecha >= CURDATE()
         ORDER BY c.fecha ASC
         LIMIT 1"
    );
    $carrera = $stmt->fetch();
    if ($carrera) {
        return $carrera;
    }
    return null;
}

/**
 * Obtiene la ultima temporada (la activa)
 */
function temporadaActiva(PDO $pdo): ?array {
    $stmt = $pdo->query("SELECT * FROM temporadas ORDER BY id DESC LIMIT 1");
    $temporada = $stmt->fetch();
    if ($temporada) {
        return $temporada;
    }
    return null;
}

/**
 * Calcula la clasificación de pilotos (suma de puntos en una temporada)
 */
function clasificacionPilotos(PDO $pdo, int $temporadaId): array {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.nombre_usuario, eq.nombre AS equipo, 
                SUM(r.puntos) AS total_puntos
         FROM usuarios u
         LEFT JOIN equipos eq ON u.equipo = eq.id
         JOIN resultados r ON u.id = r.usuario
         JOIN carreras c ON r.carrera = c.id
         WHERE c.temporada = ?
         GROUP BY u.id
         ORDER BY total_puntos DESC"
    );
    $stmt->execute([$temporadaId]);
    return $stmt->fetchAll();
}

/**
 * Calcula la clasificación de equipos (suma de puntos del equipo en una temporada)
 */
function clasificacionEquipos(PDO $pdo, int $temporadaId): array {
    $stmt = $pdo->prepare(
        "SELECT eq.id, eq.nombre, 
                SUM(r.puntos) AS total_puntos
         FROM equipos eq
         JOIN usuarios u ON eq.id = u.equipo
         JOIN resultados r ON u.id = r.usuario
         JOIN carreras c ON r.carrera = c.id
         WHERE c.temporada = ?
         GROUP BY eq.id
         ORDER BY total_puntos DESC"
    );
    $stmt->execute([$temporadaId]);
    return $stmt->fetchAll();
}

/**
 * Convierte milisegundos a formato de carrera (MM:SS.mmm o HH:MM:SS.mmm)
 */
function formatoTiempo(int $ms): string {
    if ($ms <= 0) {
        return '--:--.---';
    }

    $horas = floor($ms / 3600000);
    $minutos = floor(($ms % 3600000) / 60000);
    $segundos = floor(($ms % 60000) / 1000);
    $milis = $ms % 1000;

    if ($horas > 0) {
        return sprintf("%02d:%02d:%02d.%03d", $horas, $minutos, $segundos, $milis);
    }
    return sprintf("%02d:%02d.%03d", $minutos, $segundos, $milis);
}

/**
 * Convierte un tiempo en texto (HH:MM:SS.mmm o MM:SS.mmm) a milisegundos (número entero)
 */
function tiempoAMilisegundos(string $tiempo): int {
    $tiempo = trim($tiempo);
    if (empty($tiempo) || $tiempo === '--:--.---') {
        return 0;
    }

    // El formato que esperamos es algo como: MM:SS.mmm o HH:MM:SS.mmm
    // Si viene vacío o con el placeholder, devolvemos 0
    
    $partes = explode(':', $tiempo);
    $horas = 0;
    $minutos = 0;
    $segundos_milis = "";

    if (count($partes) === 3) {
        $horas = (int)$partes[0];
        $minutos = (int)$partes[1];
        $segundos_milis = $partes[2];
    } elseif (count($partes) === 2) {
        $minutos = (int)$partes[0];
        $segundos_milis = $partes[1];
    } else {
        $segundos_milis = $partes[0];
    }

    $sub_partes = explode('.', $segundos_milis);
    $segundos = (int)$sub_partes[0];
    $milis = 0;
    if (isset($sub_partes[1])) {
        $milis = (int)str_pad($sub_partes[1], 3, '0', STR_PAD_RIGHT);
    }

    return ($horas * 3600000) + ($minutos * 60000) + ($segundos * 1000) + $milis;
}

/**
 * Cuenta cuántos registros hay en una tabla (opcionalmente con un WHERE)
 */
function contarRegistros(PDO $pdo, string $tabla, string $where = ''): int {
    $tablas_validas = ['usuarios', 'equipos', 'carreras', 'temporadas', 'inscripciones', 'resultados', 'sanciones'];
    if (!in_array($tabla, $tablas_validas)) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) FROM $tabla";
    if ($where) {
        $sql .= " WHERE $where";
    }
    
    $stmt = $pdo->query($sql);
    return (int) $stmt->fetchColumn();
}
