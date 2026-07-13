<?php
/**
 * Procesa el formulario de contacto de soporte.html.
 * Si hay base de datos, guarda el mensaje en `mensajes_contacto`.
 * Si NO hay base de datos disponible, el mensaje no se pierde:
 * se guarda como respaldo en includes/data/contacto_respaldo.jsonl
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$nombre  = trim($_POST['nombre']  ?? '');
$correo  = trim($_POST['correo']  ?? '');
$asunto  = trim($_POST['asunto']  ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if (strlen($nombre) < 2 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($mensaje) < 10) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

$guardadoEnBD = false;
$pdo = conectarDB();

if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje) VALUES (:nombre, :correo, :asunto, :mensaje)'
        );
        $stmt->execute([
            ':nombre'  => $nombre,
            ':correo'  => $correo,
            ':asunto'  => $asunto,
            ':mensaje' => $mensaje,
        ]);
        $guardadoEnBD = true;
    } catch (PDOException $e) {
        error_log('NexPlay DB: error al guardar mensaje de contacto - ' . $e->getMessage());
    }
}

if (!$guardadoEnBD) {
    // Respaldo: no hay base de datos disponible, no perdemos el mensaje.
    $carpeta = __DIR__ . '/data';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }
    $linea = json_encode([
        'nombre'    => $nombre,
        'correo'    => $correo,
        'asunto'    => $asunto,
        'mensaje'   => $mensaje,
        'fecha'     => date('c'),
    ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    file_put_contents($carpeta . '/contacto_respaldo.jsonl', $linea, FILE_APPEND | LOCK_EX);
}

echo json_encode(['ok' => true, 'guardado_en_bd' => $guardadoEnBD]);
