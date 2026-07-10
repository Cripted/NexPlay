<?php
/**
 * api/productos.php
 * -----------------------------------------------------------
 * Devuelve el catálogo de productos en JSON.
 * 1) Intenta leer de la base de datos (tabla productos + categorias).
 * 2) Si no hay conexión, o la tabla existe pero está vacía,
 *    responde con los mismos datos "de fábrica" (respaldo)
 *    para que la tienda nunca se vea vacía.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

/* ---------- Datos de respaldo (idénticos al volcado nexplayn.sql) ---------- */
function productosRespaldo(): array
{
    return [
        ['id'=>1,  'nombre'=>'PlayStation 5 Slim',            'slug'=>'ps5-slim',                 'tipo'=>'Consola',          'descripcion'=>'1TB SSD, control DualSense incluido.',            'precio'=>11499.00, 'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/console-ps.svg',     'destacado'=>1, 'stock'=>20, 'categoria_slug'=>'ps5',    'categoria_nombre'=>'PlayStation 5'],
        ['id'=>2,  'nombre'=>'Xbox Series X',                 'slug'=>'xbox-series-x',            'tipo'=>'Consola',          'descripcion'=>'1TB SSD, 4K nativo, retrocompatible.',            'precio'=>11999.00, 'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/console-xbox.svg',   'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'xbox',   'categoria_nombre'=>'Xbox Series X|S'],
        ['id'=>3,  'nombre'=>'Nintendo Switch OLED',          'slug'=>'switch-oled',              'tipo'=>'Consola',          'descripcion'=>'Pantalla OLED 7", edición estándar.',             'precio'=>7799.00,  'precio_anterior'=>8999.00,'calificacion'=>4.0, 'imagen'=>'assets/img/console-switch.svg', 'destacado'=>1, 'stock'=>20, 'categoria_slug'=>'switch', 'categoria_nombre'=>'Nintendo Switch'],
        ['id'=>4,  'nombre'=>'Cartucho Colección 16-bit',     'slug'=>'cartucho-coleccion-16bit', 'tipo'=>'Retro',            'descripcion'=>'Edición restaurada, caja e instructivo.',         'precio'=>1299.00,  'precio_anterior'=>null,   'calificacion'=>4.0, 'imagen'=>'assets/img/console-retro.svg',  'destacado'=>1, 'stock'=>20, 'categoria_slug'=>'retro',  'categoria_nombre'=>'Retrogaming'],
        ['id'=>5,  'nombre'=>'Headset Inalámbrico Pro',       'slug'=>'headset-inalambrico-pro',  'tipo'=>'Accesorio',        'descripcion'=>'Sonido envolvente 7.1, batería 20h.',             'precio'=>1899.00,  'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/acc-headset.svg',    'destacado'=>1, 'stock'=>20, 'categoria_slug'=>'pc',     'categoria_nombre'=>'PC Gaming'],
        ['id'=>6,  'nombre'=>'Teclado Mecánico RGB',          'slug'=>'teclado-mecanico-rgb',     'tipo'=>'Accesorio',        'descripcion'=>'Switches táctiles, reposamuñecas incluido.',      'precio'=>1599.00,  'precio_anterior'=>null,   'calificacion'=>4.0, 'imagen'=>'assets/img/acc-keyboard.svg',   'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'pc',     'categoria_nombre'=>'PC Gaming'],
        ['id'=>7,  'nombre'=>'Control DualSense Extra',       'slug'=>'control-dualsense-extra',  'tipo'=>'Accesorio · PS5',  'descripcion'=>'Vibración háptica, gatillos adaptativos.',        'precio'=>1399.00,  'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/acc-controller.svg', 'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'ps5',    'categoria_nombre'=>'PlayStation 5'],
        ['id'=>8,  'nombre'=>'SSD NVMe 1TB Gaming',           'slug'=>'ssd-nvme-1tb-gaming',      'tipo'=>'Accesorio',        'descripcion'=>'Lectura hasta 7000 MB/s, disipador incluido.',    'precio'=>1699.00,  'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/acc-storage.svg',    'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'pc',     'categoria_nombre'=>'PC Gaming'],
        ['id'=>9,  'nombre'=>'Bundle Switch + Mario Kart',    'slug'=>'bundle-switch-mariokart',  'tipo'=>'Bundle',           'descripcion'=>'Consola + juego físico, ahorro incluido.',        'precio'=>8499.00,  'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/acc-bundle.svg',     'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'switch', 'categoria_nombre'=>'Nintendo Switch'],
        ['id'=>10, 'nombre'=>'Galaxy Quest: Edición Deluxe',  'slug'=>'galaxy-quest-deluxe',      'tipo'=>'Videojuego',       'descripcion'=>'Incluye pase de temporada y skins exclusivas.',   'precio'=>1199.00,  'precio_anterior'=>null,   'calificacion'=>4.0, 'imagen'=>'assets/img/acc-game.svg',       'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'xbox',   'categoria_nombre'=>'Xbox Series X|S'],
        ['id'=>11, 'nombre'=>'Handheld Retro Emulador',       'slug'=>'handheld-retro-emulador',  'tipo'=>'Retro',            'descripcion'=>'Miles de títulos clásicos preinstalados.',        'precio'=>2199.00,  'precio_anterior'=>null,   'calificacion'=>3.0, 'imagen'=>'assets/img/console-retro.svg',  'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'retro',  'categoria_nombre'=>'Retrogaming'],
        ['id'=>12, 'nombre'=>'Control Elite Series 2',        'slug'=>'control-elite-series-2',   'tipo'=>'Accesorio · Xbox', 'descripcion'=>'Componentes intercambiables, grip texturizado.',  'precio'=>2599.00,  'precio_anterior'=>null,   'calificacion'=>5.0, 'imagen'=>'assets/img/acc-controller.svg', 'destacado'=>0, 'stock'=>20, 'categoria_slug'=>'xbox',   'categoria_nombre'=>'Xbox Series X|S'],
    ];
}

$pdo = conectarDB();
$productos = [];
$fuente = 'respaldo'; // 'db' | 'respaldo'

if ($pdo !== null) {
    try {
        $sql = "SELECT p.id, p.nombre, p.slug, p.tipo, p.descripcion, p.precio,
                       p.precio_anterior, p.calificacion, p.imagen, p.destacado, p.stock,
                       c.slug   AS categoria_slug,
                       c.nombre AS categoria_nombre
                FROM productos p
                INNER JOIN categorias c ON c.id = p.categoria_id
                ORDER BY p.id ASC";
        $stmt = $pdo->query($sql);
        $filas = $stmt->fetchAll();

        if (count($filas) > 0) {
            $productos = $filas;
            $fuente = 'db';
        }
    } catch (PDOException $e) {
        // La tabla probablemente no existe todavía: seguimos con respaldo
        error_log('NexPlay productos.php: ' . $e->getMessage());
    }
}

if (empty($productos)) {
    $productos = productosRespaldo();
    $fuente = 'respaldo';
}

echo json_encode([
    'ok'        => true,
    'fuente'    => $fuente, // útil para depurar: 'db' = vino de MySQL, 'respaldo' = datos fijos
    'total'     => count($productos),
    'productos' => $productos,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
