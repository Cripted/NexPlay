<?php
/**
 * api/blog.php
 * -----------------------------------------------------------
 * Devuelve los artículos del blog en JSON.
 * 1) Intenta leer de la base de datos (tabla blog_posts).
 * 2) Si no hay conexión, o la tabla existe pero está vacía,
 *    responde con los mismos artículos "de fábrica" (respaldo).
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

/* ---------- Datos de respaldo (idénticos al volcado nexplayn.sql) ---------- */
function blogRespaldo(): array
{
    return [
        [
            'id' => 1, 'titulo' => 'Nova Ronin: el shooter que redefine el género este año',
            'slug' => 'nova-ronin-reseña', 'categoria' => 'Reseña',
            'extracto' => 'Analizamos a fondo el título más comentado del trimestre: combate táctico, narrativa ramificada y un apartado técnico que exige hardware de última generación.',
            'contenido' => 'Nova Ronin combina un sistema de coberturas dinámico con decisiones narrativas que alteran el tercio final de la campaña. El rendimiento en PS5 y Series X se mantiene estable en 60 fps con ray tracing activado, mientras que la versión de PC ofrece soporte nativo para ultrawide. El multijugador competitivo, aunque secundario, suma valor de rejugabilidad. Veredicto: sobresaliente en campaña, correcto en multijugador.',
            'imagen' => 'assets/img/article-resena.svg', 'fecha_publicacion' => '2026-07-08',
        ],
        [
            'id' => 2, 'titulo' => 'Primeras impresiones de Chrono Break: mundo abierto y viajes en el tiempo',
            'slug' => 'chrono-break-avance', 'categoria' => 'Avance',
            'extracto' => 'Tuvimos acceso anticipado a dos horas de gameplay. Te contamos qué esperar del sistema de líneas temporales y su fecha de lanzamiento confirmada.',
            'contenido' => 'El estudio confirmó que Chrono Break llegará a PS5, Xbox Series X|S y PC en el último trimestre del año. El sistema de "bifurcaciones" permite que decisiones tomadas en una época alteren directamente el escenario de otra, un mecanismo que hasta ahora se siente fresco y bien pulido. La demo mostrada no presentó caídas de frames, aunque el estudio aclaró que sigue en optimización.',
            'imagen' => 'assets/img/article-avance.svg', 'fecha_publicacion' => '2026-07-03',
        ],
        [
            'id' => 3, 'titulo' => 'Summer Game Fest 2026: los cinco anuncios más importantes',
            'slug' => 'summer-game-fest-2026', 'categoria' => 'Evento',
            'extracto' => 'Desde nuevas entregas de sagas clásicas hasta sorpresas de estudios independientes: un resumen de lo más relevante del evento.',
            'contenido' => 'Entre los anuncios destacó el regreso de una franquicia de rol táctico ausente desde hace ocho años, además de una oleada de estudios independientes mexicanos presentando proyectos con apoyo de publishers internacionales. También se confirmaron fechas de lanzamiento para dos de los títulos más esperados del próximo año, ambos con versión física confirmada para Latinoamérica.',
            'imagen' => 'assets/img/article-evento.svg', 'fecha_publicacion' => '2026-06-28',
        ],
        [
            'id' => 4, 'titulo' => '¿Qué consola elegir en 2026 según tu presupuesto?',
            'slug' => 'que-consola-elegir-2026', 'categoria' => 'Guía de compra',
            'extracto' => 'Comparamos PS5, Xbox Series X|S, Switch OLED y opciones retro para cada perfil de comprador: casual, hardcore, regalo y estudiante.',
            'contenido' => 'Si tu prioridad es exclusivos narrativos, PS5 sigue siendo la opción más sólida. Para quienes buscan retrocompatibilidad y Game Pass, Xbox Series S ofrece la mejor relación precio-beneficio de la generación. Si el uso es mixto entre sala y viajes, Switch OLED continúa siendo insustituible. Para presupuestos ajustados, un handheld retro con emulación cubre cientos de clásicos por una fracción del costo.',
            'imagen' => 'assets/img/article-guia.svg', 'fecha_publicacion' => '2026-06-20',
        ],
        [
            'id' => 5, 'titulo' => 'Así construye la comunidad NexPlay sus wikis colaborativas',
            'slug' => 'wikis-colaborativas-nexplay', 'categoria' => 'Comunidad',
            'extracto' => 'Insignias, créditos y moderación por pares: el sistema gamificado que convierte a los jugadores en editores.',
            'contenido' => 'Cada contribución verificada otorga créditos canjeables dentro de la tienda y suma progreso hacia insignias de perfil. La moderación combina revisión comunitaria con validadores de confianza, un modelo que ha reducido el vandalismo de contenido y mantenido actualizadas las guías de los títulos más jugados del catálogo.',
            'imagen' => 'assets/img/gallery-wiki.svg', 'fecha_publicacion' => '2026-06-14',
        ],
        [
            'id' => 6, 'titulo' => 'Torneo NexPlay de junio: así se vivió la gran final',
            'slug' => 'torneo-nexplay-junio', 'categoria' => 'Esports',
            'extracto' => 'Ocho equipos, un premio en crédito de tienda y una final que se definió en el último round.',
            'contenido' => 'La final reunió a los dos equipos con mejor récord de la temporada regular en un formato al mejor de cinco. El equipo ganador recibió crédito de tienda canjeable en cualquier categoría del catálogo, además de una insignia exclusiva de "Campeón de Temporada" visible en su perfil de comunidad.',
            'imagen' => 'assets/img/gallery-torneo.svg', 'fecha_publicacion' => '2026-06-09',
        ],
    ];
}

$pdo = conectarDB();
$posts = [];
$fuente = 'respaldo';

if ($pdo !== null) {
    try {
        $sql = "SELECT id, titulo, slug, categoria, extracto, contenido, imagen, fecha_publicacion
                FROM blog_posts
                ORDER BY fecha_publicacion DESC";
        $stmt = $pdo->query($sql);
        $filas = $stmt->fetchAll();

        if (count($filas) > 0) {
            $posts = $filas;
            $fuente = 'db';
        }
    } catch (PDOException $e) {
        error_log('NexPlay blog.php: ' . $e->getMessage());
    }
}

if (empty($posts)) {
    $posts = blogRespaldo();
    $fuente = 'respaldo';
}

echo json_encode([
    'ok'    => true,
    'fuente'=> $fuente,
    'total' => count($posts),
    'posts' => $posts,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
