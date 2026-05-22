<?php
// ==================================
// FUNCIONES DE AUTENTIFICACIÓN
// ==================================

function estaLogueado(): bool {
    return isset($_SESSION['usuario_id']);
}

function esAdmin(): bool {
    if (!estaLogueado()) {
        return false;
    }
    $rango = '';
    if (isset($_SESSION['rango_piloto'])) {
        $rango = $_SESSION['rango_piloto'];
    }
    return $rango === 'admin';
}

function esModerador(): bool {
    return esAdmin();
}

function requiereLogin(): void {
    if (!estaLogueado()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

function requiereAdmin(): void {
    if (!esAdmin()) {
        header('Location: ' . SITE_URL . 'index.php');
        exit;
    }
}

function usuarioActual(PDO $pdo): ?array {
    if (!estaLogueado()){
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $user = $stmt->fetch();
    if ($user) {
        return $user;
    }
    return null;
}

function loginUsuario(PDO $pdo, string $usuario, string $password): bool {
    $stmt = $pdo->prepare(
        "SELECT * FROM usuarios WHERE nombre_usuario = ? OR email = ? LIMIT 1"
    );
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['contraseña'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
        $rango = 'piloto';
        if (isset($user['rango_piloto']) && $user['rango_piloto'] !== '') {
            $rango = $user['rango_piloto'];
        }
        $_SESSION['rango_piloto'] = $rango;
        return true;
    }
    return false;
}

function registrarUsuario(PDO $pdo, array $datos): array {
    // Validaciones
    $errores = [];

    if (empty($datos['nombre_usuario']) || strlen($datos['nombre_usuario']) < 3) {
        $errores[] = 'El nombre de usuario debe tener al menos 3 caracteres.';
    }
    if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Email no válido.';
    }
    if (empty($datos['password']) || strlen($datos['password']) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if (empty($errores)) {
        // Comprobar duplicados
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR email = ?");
        $stmt->execute([$datos['nombre_usuario'], $datos['email']]);
        if ($stmt->fetch()) {
            $errores[] = 'El nombre de usuario o email ya están registrados.';
        }
    }

    if (empty($errores)) {
        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nombre_usuario, email, contraseña)
             VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $datos['nombre_usuario'],
            $datos['email'],
            $hash
        ]);

        // Auto-login
        $_SESSION['usuario_id'] = $pdo->lastInsertId();
        $_SESSION['nombre_usuario'] = $datos['nombre_usuario'];
        $_SESSION['rango_piloto'] = 'piloto';
    }

    return $errores;
}

function cerrarSesion(): void {
    session_destroy();
    header('Location: ' . SITE_URL . 'index.php');
    exit;
}
