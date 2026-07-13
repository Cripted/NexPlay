<?php
require_once __DIR__ . '/db.php';

/**
 * Cada función "obtener..." intenta leer de MySQL primero.
 * Si no hay conexión, la tabla no existe todavía, o la consulta
 * regresa vacío, se usan los datos de respaldo (los mismos que
 * traía el sitio estático originalmente) para que la página
 * nunca se vea rota o vacía.
 */

// =====================================================================
// PRODUCTOS
// =====================================================================

function obtenerProductos(): array
{
    $pdo = conectarDB();
    if ($pdo) {
        try {
            $stmt = $pdo->query(
                "SELECT p.id, p.nombre, p.tipo, p.descripcion, p.precio, p.precio_anterior,
                        p.calificacion, p.imagen, p.destacado, c.slug AS plataforma
                 FROM productos p
                 JOIN categorias c ON c.id = p.categoria_id
                 ORDER BY p.id"
            );
            $filas = $stmt->fetchAll();
            if ($filas) {
                return $filas;
            }
        } catch (PDOException $e) {
            error_log('NexPlay DB: error al leer productos - ' . $e->getMessage());
        }
    }
    return productosRespaldo();
}

function obtenerProductosDestacados(int $limite = 4): array
{
    $productos = obtenerProductos();
    $destacados = array_values(array_filter($productos, fn($p) => (int)$p['destacado'] === 1));
    if (!$destacados) {
        $destacados = $productos; // si ninguno está marcado como destacado, muestra los primeros
    }
    return array_slice($destacados, 0, $limite);
}

function productosRespaldo(): array
{
    return [
        ['id' => 1,  'plataforma' => 'ps5',    'nombre' => 'PlayStation 5 Slim',           'tipo' => 'Consola',           'descripcion' => '1TB SSD, control DualSense incluido.',          'precio' => 11499, 'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/console-ps.svg',     'destacado' => 1],
        ['id' => 2,  'plataforma' => 'xbox',   'nombre' => 'Xbox Series X',                'tipo' => 'Consola',           'descripcion' => '1TB SSD, 4K nativo, retrocompatible.',           'precio' => 11999, 'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/console-xbox.svg',   'destacado' => 0],
        ['id' => 3,  'plataforma' => 'switch', 'nombre' => 'Switch OLED',                  'tipo' => 'Consola',           'descripcion' => 'Pantalla OLED 7", edición estándar.',            'precio' => 7799,  'precio_anterior' => 8999,  'calificacion' => 4, 'imagen' => 'assets/img/console-switch.svg', 'destacado' => 1],
        ['id' => 4,  'plataforma' => 'retro',  'nombre' => 'Cartucho Colección 16-bit',    'tipo' => 'Retro',             'descripcion' => 'Edición restaurada, caja e instructivo.',       'precio' => 1299,  'precio_anterior' => null, 'calificacion' => 4, 'imagen' => 'assets/img/console-retro.svg',  'destacado' => 1],
        ['id' => 5,  'plataforma' => 'pc',     'nombre' => 'Headset Inalámbrico Pro',      'tipo' => 'Accesorio',         'descripcion' => 'Sonido envolvente 7.1, batería 20h.',           'precio' => 1899,  'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/acc-headset.svg',    'destacado' => 1],
        ['id' => 6,  'plataforma' => 'pc',     'nombre' => 'Teclado Mecánico RGB',         'tipo' => 'Accesorio',         'descripcion' => 'Switches táctiles, reposamuñecas incluido.',    'precio' => 1599,  'precio_anterior' => null, 'calificacion' => 4, 'imagen' => 'assets/img/acc-keyboard.svg',   'destacado' => 0],
        ['id' => 7,  'plataforma' => 'ps5',    'nombre' => 'Control DualSense Extra',      'tipo' => 'Accesorio · PS5',   'descripcion' => 'Vibración háptica, gatillos adaptativos.',      'precio' => 1399,  'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/acc-controller.svg', 'destacado' => 0],
        ['id' => 8,  'plataforma' => 'pc',     'nombre' => 'SSD NVMe 1TB Gaming',          'tipo' => 'Accesorio',         'descripcion' => 'Lectura hasta 7000 MB/s, disipador incluido.',  'precio' => 1699,  'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/acc-storage.svg',    'destacado' => 0],
        ['id' => 9,  'plataforma' => 'switch', 'nombre' => 'Bundle Switch + Mario Kart',   'tipo' => 'Bundle',            'descripcion' => 'Consola + juego físico, ahorro incluido.',      'precio' => 8499,  'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/acc-bundle.svg',     'destacado' => 0],
        ['id' => 10, 'plataforma' => 'xbox',   'nombre' => 'Galaxy Quest: Edición Deluxe', 'tipo' => 'Videojuego',        'descripcion' => 'Incluye pase de temporada y skins exclusivas.', 'precio' => 1199,  'precio_anterior' => null, 'calificacion' => 4, 'imagen' => 'assets/img/acc-game.svg',       'destacado' => 0],
        ['id' => 11, 'plataforma' => 'retro',  'nombre' => 'Handheld Retro Emulador',      'tipo' => 'Retro',             'descripcion' => 'Miles de títulos clásicos preinstalados.',      'precio' => 2199,  'precio_anterior' => null, 'calificacion' => 3, 'imagen' => 'assets/img/console-retro.svg',  'destacado' => 0],
        ['id' => 12, 'plataforma' => 'xbox',   'nombre' => 'Control Elite Series 2',       'tipo' => 'Accesorio · Xbox',  'descripcion' => 'Componentes intercambiables, grip texturizado.', 'precio' => 2599, 'precio_anterior' => null, 'calificacion' => 5, 'imagen' => 'assets/img/acc-controller.svg', 'destacado' => 0],
    ];
}

// =====================================================================
// BLOG
// =====================================================================

function obtenerBlogPosts(): array
{
    $pdo = conectarDB();
    if ($pdo) {
        try {
            $stmt = $pdo->query(
                "SELECT id, titulo, categoria, extracto, contenido, imagen, fecha_publicacion
                 FROM blog_posts
                 ORDER BY fecha_publicacion DESC"
            );
            $filas = $stmt->fetchAll();
            if ($filas) {
                return $filas;
            }
        } catch (PDOException $e) {
            error_log('NexPlay DB: error al leer blog_posts - ' . $e->getMessage());
        }
    }
    return blogRespaldo();
}

function blogRespaldo(): array
{
    return [
        ['id' => 1, 'titulo' => 'Nova Ronin: el shooter que redefine el género este año', 'categoria' => 'Reseña',
         'extracto' => 'Analizamos a fondo el título más comentado del trimestre: combate táctico, narrativa ramificada y un apartado técnico que exige hardware de última generación.',
         'contenido' => 'Nova Ronin combina un sistema de coberturas dinámico con decisiones narrativas que alteran el tercio final de la campaña. El rendimiento en PS5 y Series X se mantiene estable en 60 fps con ray tracing activado, mientras que la versión de PC ofrece soporte nativo para ultrawide. El multijugador competitivo, aunque secundario, suma valor de rejugabilidad. Veredicto: sobresaliente en campaña, correcto en multijugador.',
         'imagen' => 'assets/img/article-resena.svg', 'fecha_publicacion' => '2026-07-08'],

        ['id' => 2, 'titulo' => 'Primeras impresiones de Chrono Break: mundo abierto y viajes en el tiempo', 'categoria' => 'Avance',
         'extracto' => 'Tuvimos acceso anticipado a dos horas de gameplay. Te contamos qué esperar del sistema de líneas temporales y su fecha de lanzamiento confirmada.',
         'contenido' => 'El estudio confirmó que Chrono Break llegará a PS5, Xbox Series X|S y PC en el último trimestre del año. El sistema de "bifurcaciones" permite que decisiones tomadas en una época alteren directamente el escenario de otra, un mecanismo que hasta ahora se siente fresco y bien pulido. La demo mostrada no presentó caídas de frames, aunque el estudio aclaró que sigue en optimización.',
         'imagen' => 'assets/img/article-avance.svg', 'fecha_publicacion' => '2026-07-03'],

        ['id' => 3, 'titulo' => 'Summer Game Fest 2026: los cinco anuncios más importantes', 'categoria' => 'Evento',
         'extracto' => 'Desde nuevas entregas de sagas clásicas hasta sorpresas de estudios independientes: un resumen de lo más relevante del evento.',
         'contenido' => 'Entre los anuncios destacó el regreso de una franquicia de rol táctico ausente desde hace ocho años, además de una oleada de estudios independientes mexicanos presentando proyectos con apoyo de publishers internacionales. También se confirmaron fechas de lanzamiento para dos de los títulos más esperados del próximo año, ambos con versión física confirmada para Latinoamérica.',
         'imagen' => 'assets/img/article-evento.svg', 'fecha_publicacion' => '2026-06-28'],

        ['id' => 4, 'titulo' => '¿Qué consola elegir en 2026 según tu presupuesto?', 'categoria' => 'Guía de compra',
         'extracto' => 'Comparamos PS5, Xbox Series X|S, Switch OLED y opciones retro para cada perfil de comprador: casual, hardcore, regalo y estudiante.',
         'contenido' => 'Si tu prioridad es exclusivos narrativos, PS5 sigue siendo la opción más sólida. Para quienes buscan retrocompatibilidad y Game Pass, Xbox Series S ofrece la mejor relación precio-beneficio de la generación. Si el uso es mixto entre sala y viajes, Switch OLED continúa siendo insustituible. Para presupuestos ajustados, un handheld retro con emulación cubre cientos de clásicos por una fracción del costo.',
         'imagen' => 'assets/img/article-guia.svg', 'fecha_publicacion' => '2026-06-20'],

        ['id' => 5, 'titulo' => 'Así construye la comunidad NexPlay sus wikis colaborativas', 'categoria' => 'Comunidad',
         'extracto' => 'Insignias, créditos y moderación por pares: el sistema gamificado que convierte a los jugadores en editores.',
         'contenido' => 'Cada contribución verificada otorga créditos canjeables dentro de la tienda y suma progreso hacia insignias de perfil. La moderación combina revisión comunitaria con validadores de confianza, un modelo que ha reducido el vandalismo de contenido y mantenido actualizadas las guías de los títulos más jugados del catálogo.',
         'imagen' => 'assets/img/gallery-wiki.svg', 'fecha_publicacion' => '2026-06-14'],

        ['id' => 6, 'titulo' => 'Torneo NexPlay de junio: así se vivió la gran final', 'categoria' => 'Esports',
         'extracto' => 'Ocho equipos, un premio en crédito de tienda y una final que se definió en el último round.',
         'contenido' => 'La final reunió a los dos equipos con mejor récord de la temporada regular en un formato al mejor de cinco. El equipo ganador recibió crédito de tienda canjeable en cualquier categoría del catálogo, además de una insignia exclusiva de "Campeón de Temporada" visible en su perfil de comunidad.',
         'imagen' => 'assets/img/gallery-torneo.svg', 'fecha_publicacion' => '2026-06-09'],
    ];
}

// =====================================================================
// AYUDANTES DE PRESENTACIÓN (usados por las páginas .php)
// =====================================================================

function renderEstrellas(float $calificacion): string
{
    $llenas = (int) round($calificacion);
    $llenas = max(0, min(5, $llenas));
    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

function formatoPrecio(float $precio): string
{
    return '$' . number_format($precio, 0, '.', ',');
}

function fechaEs(string $fechaISO): string
{
    $meses = [1=>'ene',2=>'feb',3=>'mar',4=>'abr',5=>'may',6=>'jun',
              7=>'jul',8=>'ago',9=>'sep',10=>'oct',11=>'nov',12=>'dic'];
    $ts = strtotime($fechaISO);
    return (int)date('j', $ts) . ' ' . $meses[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

function badgeClase(string $categoria): string
{
    return match ($categoria) {
        'Reseña', 'Guía de compra' => 'badge purple',
        'Avance', 'Comunidad'      => 'badge cyan',
        default                    => 'badge', // Evento, Esports
    };
}
