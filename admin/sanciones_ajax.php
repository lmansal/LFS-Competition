<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!esAdmin()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = 0;
    if (isset($_POST['usuario']) && $_POST['usuario'] !== '') {
        $usuario = (int)$_POST['usuario'];
    } elseif (isset($_POST['usuario_id']) && $_POST['usuario_id'] !== '') {
        $usuario = (int)$_POST['usuario_id'];
    }

    $carreraRaw = null;
    if (isset($_POST['carrera'])) {
        $carreraRaw = $_POST['carrera'];
    } elseif (isset($_POST['carrera_id'])) {
        $carreraRaw = $_POST['carrera_id'];
    }

    $carrera = null;
    if ($carreraRaw !== null && $carreraRaw !== '') {
        $carrera = (int)$carreraRaw;
    }

    $tipo = '';
    if (isset($_POST['tipo'])) {
        $tipo = trim($_POST['tipo']);
    }
    $desc = '';
    if (isset($_POST['descripcion'])) {
        $desc = trim($_POST['descripcion']);
    }

    if (!$usuario || !$tipo) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO sanciones (usuario, carrera, tipo, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario, $carrera, $tipo, $desc]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
