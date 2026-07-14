<?php
ob_start();

/**
 * includes/db.php — Conexión a la base de datos NexPlay
 * Detecta automáticamente si está en localhost (XAMPP) o producción (atspace.cc)
 */

// ── Detección automática de entorno ─────────────────────────────────────────
$_np_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_np_isLocal = ($_np_host === 'localhost' || $_np_host === '127.0.0.1'
             || str_starts_with($_np_host, 'localhost:'));

if ($_np_isLocal) {
    // ── DESARROLLO (XAMPP local) ─────────────────────────────────────────────
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'nexplay');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/NexPlay');
} else {
    // ── PRODUCCIÓN (atspace.cc) ──────────────────────────────────────────────
    define('DB_HOST', 'fdb1032.atspace.me');
    define('DB_NAME', '4714565_nexplay');
    define('DB_USER', '4714565_nexplay');
    define('DB_PASS', 'Hectoroscar!23l3');
    if (!defined('SITE_URL')) define('SITE_URL', 'http://nex-play.atspace.cc');
}

unset($_np_host, $_np_isLocal);

// ── Constantes generales (solo si no están definidas) ────────────────────────
if (!defined('SESSION_NAME'))     define('SESSION_NAME',     'np_sess');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 7200);
if (!defined('UPLOAD_DIR'))       define('UPLOAD_DIR',       __DIR__ . '/../uploads/');
if (!defined('UPLOAD_URL'))       define('UPLOAD_URL',       SITE_URL . '/uploads/');
if (!defined('MAX_FILE_SIZE'))    define('MAX_FILE_SIZE',    5242880);
if (!defined('ALLOWED_IMAGES'))   define('ALLOWED_IMAGES',   ['jpg','jpeg','png','gif','webp']);

date_default_timezone_set('America/Mexico_City');

// ── Conexión PDO principal (conectarDB) ──────────────────────────────────────
function conectarDB(): ?PDO
{
    static $pdo      = null;
    static $intentado = false;

    if ($intentado) return $pdo;
    $intentado = true;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('NexPlay DB: no se pudo conectar - ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

// ── Conexión PDO alternativa (getDB) — compatible con admin y apis ───────────
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Conexión PDO fallida: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ── Conexión MySQLi (para compatibilidad legacy) ─────────────────────────────
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log('NexPlay MySQLi: ' . $conn->connect_error);
        $conn = null;
    } else {
        $conn->set_charset("utf8mb4");
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
if (!function_exists('sanitize')) {
    function sanitize($v) {
        if (is_array($v)) return array_map('sanitize', $v);
        return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generateSlug')) {
    function generateSlug($t) {
        $t = mb_strtolower($t, 'UTF-8');
        $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
        $t = preg_replace('/[^a-z0-9\s-]/', '', $t);
        $t = preg_replace('/[\s-]+/', '-', $t);
        return trim($t, '-');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($d) {
        $m = ['January'=>'Enero','February'=>'Febrero','March'=>'Marzo','April'=>'Abril',
              'May'=>'Mayo','June'=>'Junio','July'=>'Julio','August'=>'Agosto',
              'September'=>'Septiembre','October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre'];
        return strtr(date('d \d\e F, Y', strtotime($d)), $m);
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        ob_end_clean();
        header("Location: $url");
        exit();
    }
}

if (!function_exists('_sess')) {
    function _sess() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }
}

if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $msg) {
        _sess();
        $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
    }
}

if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        _sess();
        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $f;
        }
        return null;
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage($file, $prefix = 'img') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK)
            return ['success'=>false,'message'=>'Error al subir el archivo'];
        if ($file['size'] > MAX_FILE_SIZE)
            return ['success'=>false,'message'=>'Archivo demasiado grande (máx 5MB)'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMAGES))
            return ['success'=>false,'message'=>'Tipo de archivo no permitido'];
        $name = $prefix.'_'.uniqid().'_'.time().'.'.$ext;
        if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
        if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR.$name))
            return ['success'=>true,'filename'=>$name,'url'=>UPLOAD_URL.$name];
        return ['success'=>false,'message'=>'Error al mover el archivo'];
    }
}
