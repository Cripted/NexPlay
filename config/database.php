<?php
/**
 * Conexión a la base de datos NexPlay (config/database.php)
 * -----------------------------------------------------------
 * Ajusta estos 4 valores según tu instalación local
 * (XAMPP / WAMP / Laragon suelen usar usuario "root" y
 * contraseña vacía por defecto).
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'nexplayn');   // nombre de la base importada desde nexplayn.sql
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Devuelve una conexión PDO activa, o null si no fue posible conectar
 * (servidor apagado, credenciales incorrectas, base no importada, etc.)
 * Nunca lanza un error fatal: las páginas que la usan deben revisar
 * si el resultado es null y, en ese caso, mostrar datos de respaldo.
 */
function conectarDB(): ?PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $opciones);
    } catch (PDOException $e) {
        // No detenemos la ejecución: solo lo dejamos en el log del servidor
        error_log('NexPlay DB: no se pudo conectar -> ' . $e->getMessage());
        return null;
    }
}
