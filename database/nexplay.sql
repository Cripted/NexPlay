-- =====================================================================
-- Base de datos: nexplay
-- Sitio: NexPlay · Tienda de Videojuegos, Consolas y Comunidad Gamer
-- Uso: importar este archivo desde phpMyAdmin (pestaña "Importar")
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `nexplay`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `nexplay`;

-- ---------------------------------------------------------------------
-- 1. Categorías / plataformas (PS5, Xbox, Switch, PC, Retro)
-- ---------------------------------------------------------------------
CREATE TABLE `categorias` (
  `id`     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(60)  NOT NULL,
  `slug`   VARCHAR(60)  NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categorias` (`nombre`, `slug`) VALUES
('PlayStation 5',      'ps5'),
('Xbox Series X|S',    'xbox'),
('Nintendo Switch',    'switch'),
('PC Gaming',          'pc'),
('Retrogaming',        'retro');

-- ---------------------------------------------------------------------
-- 2. Productos (catálogo de la tienda)
-- ---------------------------------------------------------------------
CREATE TABLE `productos` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `categoria_id`      INT UNSIGNED NOT NULL,
  `nombre`            VARCHAR(150) NOT NULL,
  `slug`              VARCHAR(160) NOT NULL UNIQUE,
  `tipo`              VARCHAR(40)  NOT NULL,      -- Consola, Accesorio, Videojuego, Bundle, Retro
  `descripcion`       VARCHAR(255) NOT NULL,
  `precio`            DECIMAL(10,2) NOT NULL,
  `precio_anterior`   DECIMAL(10,2) NULL,          -- NULL si no hay descuento
  `calificacion`      DECIMAL(2,1) NOT NULL DEFAULT 0,  -- 0.0 a 5.0
  `imagen`            VARCHAR(255) NOT NULL,
  `destacado`         TINYINT(1)   NOT NULL DEFAULT 0,
  `stock`             INT UNSIGNED NOT NULL DEFAULT 20,
  `creado_en`         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `productos`
  (`categoria_id`, `nombre`, `slug`, `tipo`, `descripcion`, `precio`, `precio_anterior`, `calificacion`, `imagen`, `destacado`)
VALUES
(1, 'PlayStation 5 Slim',            'ps5-slim',                 'Consola',            '1TB SSD, control DualSense incluido.',            11499.00, NULL,     5.0, 'assets/img/console-ps.svg',       1),
(2, 'Xbox Series X',                 'xbox-series-x',            'Consola',            '1TB SSD, 4K nativo, retrocompatible.',             11999.00, NULL,     5.0, 'assets/img/console-xbox.svg',     0),
(3, 'Nintendo Switch OLED',          'switch-oled',              'Consola',            'Pantalla OLED 7", edición estándar.',              7799.00,  8999.00, 4.0, 'assets/img/console-switch.svg',   1),
(5, 'Cartucho Colección 16-bit',     'cartucho-coleccion-16bit', 'Retro',              'Edición restaurada, caja e instructivo.',           1299.00,  NULL,     4.0, 'assets/img/console-retro.svg',    1),
(4, 'Headset Inalámbrico Pro',       'headset-inalambrico-pro',  'Accesorio',          'Sonido envolvente 7.1, batería 20h.',               1899.00,  NULL,     5.0, 'assets/img/acc-headset.svg',      1),
(4, 'Teclado Mecánico RGB',          'teclado-mecanico-rgb',     'Accesorio',          'Switches táctiles, reposamuñecas incluido.',        1599.00,  NULL,     4.0, 'assets/img/acc-keyboard.svg',     0),
(1, 'Control DualSense Extra',       'control-dualsense-extra',  'Accesorio · PS5',    'Vibración háptica, gatillos adaptativos.',          1399.00,  NULL,     5.0, 'assets/img/acc-controller.svg',   0),
(4, 'SSD NVMe 1TB Gaming',           'ssd-nvme-1tb-gaming',      'Accesorio',          'Lectura hasta 7000 MB/s, disipador incluido.',      1699.00,  NULL,     5.0, 'assets/img/acc-storage.svg',      0),
(3, 'Bundle Switch + Mario Kart',    'bundle-switch-mariokart',  'Bundle',             'Consola + juego físico, ahorro incluido.',          8499.00,  NULL,     5.0, 'assets/img/acc-bundle.svg',       0),
(2, 'Galaxy Quest: Edición Deluxe',  'galaxy-quest-deluxe',      'Videojuego',         'Incluye pase de temporada y skins exclusivas.',     1199.00,  NULL,     4.0, 'assets/img/acc-game.svg',         0),
(5, 'Handheld Retro Emulador',       'handheld-retro-emulador',  'Retro',              'Miles de títulos clásicos preinstalados.',          2199.00,  NULL,     3.0, 'assets/img/console-retro.svg',    0),
(2, 'Control Elite Series 2',       'control-elite-series-2',   'Accesorio · Xbox',   'Componentes intercambiables, grip texturizado.',    2599.00,  NULL,     5.0, 'assets/img/acc-controller.svg',   0);

-- ---------------------------------------------------------------------
-- 3. Publicaciones del blog
-- ---------------------------------------------------------------------
CREATE TABLE `blog_posts` (
  `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titulo`             VARCHAR(200) NOT NULL,
  `slug`               VARCHAR(220) NOT NULL UNIQUE,
  `categoria`          VARCHAR(40)  NOT NULL,   -- Reseña, Avance, Evento, Guía de compra, Comunidad, Esports
  `extracto`           VARCHAR(400) NOT NULL,
  `contenido`          TEXT         NOT NULL,
  `imagen`             VARCHAR(255) NOT NULL,
  `fecha_publicacion`  DATE         NOT NULL,
  `creado_en`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `blog_posts`
  (`titulo`, `slug`, `categoria`, `extracto`, `contenido`, `imagen`, `fecha_publicacion`)
VALUES
('Nova Ronin: el shooter que redefine el género este año',
 'nova-ronin-reseña',
 'Reseña',
 'Analizamos a fondo el título más comentado del trimestre: combate táctico, narrativa ramificada y un apartado técnico que exige hardware de última generación.',
 'Nova Ronin combina un sistema de coberturas dinámico con decisiones narrativas que alteran el tercio final de la campaña. El rendimiento en PS5 y Series X se mantiene estable en 60 fps con ray tracing activado, mientras que la versión de PC ofrece soporte nativo para ultrawide. El multijugador competitivo, aunque secundario, suma valor de rejugabilidad. Veredicto: sobresaliente en campaña, correcto en multijugador.',
 'assets/img/article-resena.svg',
 '2026-07-08'),

('Primeras impresiones de Chrono Break: mundo abierto y viajes en el tiempo',
 'chrono-break-avance',
 'Avance',
 'Tuvimos acceso anticipado a dos horas de gameplay. Te contamos qué esperar del sistema de líneas temporales y su fecha de lanzamiento confirmada.',
 'El estudio confirmó que Chrono Break llegará a PS5, Xbox Series X|S y PC en el último trimestre del año. El sistema de "bifurcaciones" permite que decisiones tomadas en una época alteren directamente el escenario de otra, un mecanismo que hasta ahora se siente fresco y bien pulido. La demo mostrada no presentó caídas de frames, aunque el estudio aclaró que sigue en optimización.',
 'assets/img/article-avance.svg',
 '2026-07-03'),

('Summer Game Fest 2026: los cinco anuncios más importantes',
 'summer-game-fest-2026',
 'Evento',
 'Desde nuevas entregas de sagas clásicas hasta sorpresas de estudios independientes: un resumen de lo más relevante del evento.',
 'Entre los anuncios destacó el regreso de una franquicia de rol táctico ausente desde hace ocho años, además de una oleada de estudios independientes mexicanos presentando proyectos con apoyo de publishers internacionales. También se confirmaron fechas de lanzamiento para dos de los títulos más esperados del próximo año, ambos con versión física confirmada para Latinoamérica.',
 'assets/img/article-evento.svg',
 '2026-06-28'),

('¿Qué consola elegir en 2026 según tu presupuesto?',
 'que-consola-elegir-2026',
 'Guía de compra',
 'Comparamos PS5, Xbox Series X|S, Switch OLED y opciones retro para cada perfil de comprador: casual, hardcore, regalo y estudiante.',
 'Si tu prioridad es exclusivos narrativos, PS5 sigue siendo la opción más sólida. Para quienes buscan retrocompatibilidad y Game Pass, Xbox Series S ofrece la mejor relación precio-beneficio de la generación. Si el uso es mixto entre sala y viajes, Switch OLED continúa siendo insustituible. Para presupuestos ajustados, un handheld retro con emulación cubre cientos de clásicos por una fracción del costo.',
 'assets/img/article-guia.svg',
 '2026-06-20'),

('Así construye la comunidad NexPlay sus wikis colaborativas',
 'wikis-colaborativas-nexplay',
 'Comunidad',
 'Insignias, créditos y moderación por pares: el sistema gamificado que convierte a los jugadores en editores.',
 'Cada contribución verificada otorga créditos canjeables dentro de la tienda y suma progreso hacia insignias de perfil. La moderación combina revisión comunitaria con validadores de confianza, un modelo que ha reducido el vandalismo de contenido y mantenido actualizadas las guías de los títulos más jugados del catálogo.',
 'assets/img/gallery-wiki.svg',
 '2026-06-14'),

('Torneo NexPlay de junio: así se vivió la gran final',
 'torneo-nexplay-junio',
 'Esports',
 'Ocho equipos, un premio en crédito de tienda y una final que se definió en el último round.',
 'La final reunió a los dos equipos con mejor récord de la temporada regular en un formato al mejor de cinco. El equipo ganador recibió crédito de tienda canjeable en cualquier categoría del catálogo, además de una insignia exclusiva de "Campeón de Temporada" visible en su perfil de comunidad.',
 'assets/img/gallery-torneo.svg',
 '2026-06-09');

-- ---------------------------------------------------------------------
-- 4. Usuarios (clientes / administradores) — para futuro login
-- ---------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre`         VARCHAR(120) NOT NULL,
  `correo`         VARCHAR(150) NOT NULL UNIQUE,
  `password_hash`  VARCHAR(255) NOT NULL,   -- usar password_hash() de PHP, nunca texto plano
  `rol`            ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  `creado_en`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 5. Pedidos (para el carrito de la tienda)
-- ---------------------------------------------------------------------
CREATE TABLE `pedidos` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id`  INT UNSIGNED NULL,
  `total`       DECIMAL(10,2) NOT NULL DEFAULT 0,
  `estado`      ENUM('pendiente','pagado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `creado_en`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pedido_items` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pedido_id`        INT UNSIGNED NOT NULL,
  `producto_id`      INT UNSIGNED NOT NULL,
  `cantidad`         INT UNSIGNED NOT NULL DEFAULT 1,
  `precio_unitario`  DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 6. Mensajes del formulario de soporte / contacto
-- ---------------------------------------------------------------------
CREATE TABLE `mensajes_contacto` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre`     VARCHAR(120) NOT NULL,
  `correo`     VARCHAR(150) NOT NULL,
  `asunto`     VARCHAR(150) NULL,
  `mensaje`    TEXT NOT NULL,
  `estado`     ENUM('nuevo','en_proceso','resuelto') NOT NULL DEFAULT 'nuevo',
  `creado_en`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 7. Suscriptores del newsletter (footer del sitio)
-- ---------------------------------------------------------------------
CREATE TABLE `newsletter_suscriptores` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `correo`        VARCHAR(150) NOT NULL UNIQUE,
  `suscrito_en`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
