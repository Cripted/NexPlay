<?php
/**
 * Conexión a la base de datos "nexplay".
 * Si el servidor MySQL no está disponible, o la base/tablas no existen,
 * conectarDB() regresa null en vez de detener la página con un error.
 * Todo el sitio revisa ese null y usa datos de respaldo (ver data.php).
 */

// --- Ajusta estos datos si tu phpMyAdmin/MySQL usa otros valores ---
const DB_HOST = 'localhost';
const DB_NAME = 'nexplay';
const DB_USER = 'root';
const DB_PASS = '';
// ---------------------------------------------------------------

function conectarDB(): ?PDO
{
    static $pdo = null;
    static $intentado = false;

    if ($intentado) {
        return $pdo; // ya se intentó antes en esta misma petición, no reintentar
    }
    $intentado = true;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // No se pudo conectar (servidor apagado, base no creada, credenciales, etc.)
        // No mostramos el error crudo al usuario final; el resto del sitio
        // seguirá funcionando con los datos de respaldo.
        error_log('NexPlay DB: no se pudo conectar - ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}
