<?php
/**
 * includes/auth.php — Capa de autenticación de NexPlay
 */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['user_logged_in'] === true;
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['user_id'],
        'nombre' => $_SESSION['user_nombre'],
        'correo' => $_SESSION['user_correo'],
        'rol'    => $_SESSION['user_rol']
    ];
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /NexPlay/pages/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_rol'] !== 'admin') {
        header('Location: /NexPlay/index.php');
        exit;
    }
}

function login(string $correo, string $password): array {
    $correo = trim($correo);
    if (empty($correo) || empty($password)) {
        return ['success' => false, 'message' => 'Por favor completa todos los campos.'];
    }

    $pdo = conectarDB();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, correo, password_hash, rol FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'El correo electrónico no está registrado.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'La contraseña es incorrecta.'];
        }

        // Configurar la sesión
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_correo'] = $user['correo'];
        $_SESSION['user_rol'] = $user['rol'];
        $_SESSION['user_logged_in'] = true;

        return ['success' => true, 'message' => 'Inicio de sesión correcto.'];
    } catch (PDOException $e) {
        error_log('Auth login error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error al procesar la solicitud.'];
    }
}

function register(string $nombre, string $correo, string $password, string $confirmar): array {
    $nombre = trim($nombre);
    $correo = trim($correo);

    if (empty($nombre) || empty($correo) || empty($password) || empty($confirmar)) {
        return ['success' => false, 'message' => 'Todos los campos son obligatorios.'];
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'El correo electrónico no es válido.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'];
    }

    if ($password !== $confirmar) {
        return ['success' => false, 'message' => 'Las contraseñas no coinciden.'];
    }

    $pdo = conectarDB();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
    }

    try {
        // Verificar duplicados
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }

        // Insertar usuario
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Si es el primer usuario, lo hacemos admin. Si no, cliente por defecto.
        $checkEmpty = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $rol = ((int)$checkEmpty === 0) ? 'admin' : 'cliente';

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password_hash, rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $correo, $hash, $rol]);

        return ['success' => true, 'message' => 'Registro completado con éxito. Ya puedes iniciar sesión.'];
    } catch (PDOException $e) {
        error_log('Auth register error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error al procesar la solicitud de registro.'];
    }
}
