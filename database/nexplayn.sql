-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-07-2026 a las 20:07:02
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.3.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nexplayn`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `categoria` varchar(40) NOT NULL,
  `extracto` varchar(400) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `titulo`, `slug`, `categoria`, `extracto`, `contenido`, `imagen`, `fecha_publicacion`, `creado_en`) VALUES
(1, 'Nova Ronin: el shooter que redefine el género este año', 'nova-ronin-reseña', 'Reseña', 'Analizamos a fondo el título más comentado del trimestre: combate táctico, narrativa ramificada y un apartado técnico que exige hardware de última generación.', 'Nova Ronin combina un sistema de coberturas dinámico con decisiones narrativas que alteran el tercio final de la campaña. El rendimiento en PS5 y Series X se mantiene estable en 60 fps con ray tracing activado, mientras que la versión de PC ofrece soporte nativo para ultrawide. El multijugador competitivo, aunque secundario, suma valor de rejugabilidad. Veredicto: sobresaliente en campaña, correcto en multijugador.', 'assets/img/article-resena.svg', '2026-07-08', '2026-07-10 20:03:01'),
(2, 'Primeras impresiones de Chrono Break: mundo abierto y viajes en el tiempo', 'chrono-break-avance', 'Avance', 'Tuvimos acceso anticipado a dos horas de gameplay. Te contamos qué esperar del sistema de líneas temporales y su fecha de lanzamiento confirmada.', 'El estudio confirmó que Chrono Break llegará a PS5, Xbox Series X|S y PC en el último trimestre del año. El sistema de \"bifurcaciones\" permite que decisiones tomadas en una época alteren directamente el escenario de otra, un mecanismo que hasta ahora se siente fresco y bien pulido. La demo mostrada no presentó caídas de frames, aunque el estudio aclaró que sigue en optimización.', 'assets/img/article-avance.svg', '2026-07-03', '2026-07-10 20:03:01'),
(3, 'Summer Game Fest 2026: los cinco anuncios más importantes', 'summer-game-fest-2026', 'Evento', 'Desde nuevas entregas de sagas clásicas hasta sorpresas de estudios independientes: un resumen de lo más relevante del evento.', 'Entre los anuncios destacó el regreso de una franquicia de rol táctico ausente desde hace ocho años, además de una oleada de estudios independientes mexicanos presentando proyectos con apoyo de publishers internacionales. También se confirmaron fechas de lanzamiento para dos de los títulos más esperados del próximo año, ambos con versión física confirmada para Latinoamérica.', 'assets/img/article-evento.svg', '2026-06-28', '2026-07-10 20:03:01'),
(4, '¿Qué consola elegir en 2026 según tu presupuesto?', 'que-consola-elegir-2026', 'Guía de compra', 'Comparamos PS5, Xbox Series X|S, Switch OLED y opciones retro para cada perfil de comprador: casual, hardcore, regalo y estudiante.', 'Si tu prioridad es exclusivos narrativos, PS5 sigue siendo la opción más sólida. Para quienes buscan retrocompatibilidad y Game Pass, Xbox Series S ofrece la mejor relación precio-beneficio de la generación. Si el uso es mixto entre sala y viajes, Switch OLED continúa siendo insustituible. Para presupuestos ajustados, un handheld retro con emulación cubre cientos de clásicos por una fracción del costo.', 'assets/img/article-guia.svg', '2026-06-20', '2026-07-10 20:03:01'),
(5, 'Así construye la comunidad NexPlay sus wikis colaborativas', 'wikis-colaborativas-nexplay', 'Comunidad', 'Insignias, créditos y moderación por pares: el sistema gamificado que convierte a los jugadores en editores.', 'Cada contribución verificada otorga créditos canjeables dentro de la tienda y suma progreso hacia insignias de perfil. La moderación combina revisión comunitaria con validadores de confianza, un modelo que ha reducido el vandalismo de contenido y mantenido actualizadas las guías de los títulos más jugados del catálogo.', 'assets/img/gallery-wiki.svg', '2026-06-14', '2026-07-10 20:03:01'),
(6, 'Torneo NexPlay de junio: así se vivió la gran final', 'torneo-nexplay-junio', 'Esports', 'Ocho equipos, un premio en crédito de tienda y una final que se definió en el último round.', 'La final reunió a los dos equipos con mejor récord de la temporada regular en un formato al mejor de cinco. El equipo ganador recibió crédito de tienda canjeable en cualquier categoría del catálogo, además de una insignia exclusiva de \"Campeón de Temporada\" visible en su perfil de comunidad.', 'assets/img/gallery-torneo.svg', '2026-06-09', '2026-07-10 20:03:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `slug`) VALUES
(1, 'PlayStation 5', 'ps5'),
(2, 'Xbox Series X|S', 'xbox'),
(3, 'Nintendo Switch', 'switch'),
(4, 'PC Gaming', 'pc'),
(5, 'Retrogaming', 'retro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--

CREATE TABLE `mensajes_contacto` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `asunto` varchar(150) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('nuevo','en_proceso','resuelto') NOT NULL DEFAULT 'nuevo',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `newsletter_suscriptores`
--

CREATE TABLE `newsletter_suscriptores` (
  `id` int(10) UNSIGNED NOT NULL,
  `correo` varchar(150) NOT NULL,
  `suscrito_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','pagado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_anterior` decimal(10,2) DEFAULT NULL,
  `calificacion` decimal(2,1) NOT NULL DEFAULT 0.0,
  `imagen` varchar(255) NOT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `slug`, `tipo`, `descripcion`, `precio`, `precio_anterior`, `calificacion`, `imagen`, `destacado`, `stock`, `creado_en`) VALUES
(1, 1, 'PlayStation 5 Slim', 'ps5-slim', 'Consola', '1TB SSD, control DualSense incluido.', 11499.00, NULL, 5.0, 'assets/img/console-ps.svg', 1, 20, '2026-07-10 20:03:01'),
(2, 2, 'Xbox Series X', 'xbox-series-x', 'Consola', '1TB SSD, 4K nativo, retrocompatible.', 11999.00, NULL, 5.0, 'assets/img/console-xbox.svg', 0, 20, '2026-07-10 20:03:01'),
(3, 3, 'Nintendo Switch OLED', 'switch-oled', 'Consola', 'Pantalla OLED 7\", edición estándar.', 7799.00, 8999.00, 4.0, 'assets/img/console-switch.svg', 1, 20, '2026-07-10 20:03:01'),
(4, 5, 'Cartucho Colección 16-bit', 'cartucho-coleccion-16bit', 'Retro', 'Edición restaurada, caja e instructivo.', 1299.00, NULL, 4.0, 'assets/img/console-retro.svg', 1, 20, '2026-07-10 20:03:01'),
(5, 4, 'Headset Inalámbrico Pro', 'headset-inalambrico-pro', 'Accesorio', 'Sonido envolvente 7.1, batería 20h.', 1899.00, NULL, 5.0, 'assets/img/acc-headset.svg', 1, 20, '2026-07-10 20:03:01'),
(6, 4, 'Teclado Mecánico RGB', 'teclado-mecanico-rgb', 'Accesorio', 'Switches táctiles, reposamuñecas incluido.', 1599.00, NULL, 4.0, 'assets/img/acc-keyboard.svg', 0, 20, '2026-07-10 20:03:01'),
(7, 1, 'Control DualSense Extra', 'control-dualsense-extra', 'Accesorio · PS5', 'Vibración háptica, gatillos adaptativos.', 1399.00, NULL, 5.0, 'assets/img/acc-controller.svg', 0, 20, '2026-07-10 20:03:01'),
(8, 4, 'SSD NVMe 1TB Gaming', 'ssd-nvme-1tb-gaming', 'Accesorio', 'Lectura hasta 7000 MB/s, disipador incluido.', 1699.00, NULL, 5.0, 'assets/img/acc-storage.svg', 0, 20, '2026-07-10 20:03:01'),
(9, 3, 'Bundle Switch + Mario Kart', 'bundle-switch-mariokart', 'Bundle', 'Consola + juego físico, ahorro incluido.', 8499.00, NULL, 5.0, 'assets/img/acc-bundle.svg', 0, 20, '2026-07-10 20:03:01'),
(10, 2, 'Galaxy Quest: Edición Deluxe', 'galaxy-quest-deluxe', 'Videojuego', 'Incluye pase de temporada y skins exclusivas.', 1199.00, NULL, 4.0, 'assets/img/acc-game.svg', 0, 20, '2026-07-10 20:03:01'),
(11, 5, 'Handheld Retro Emulador', 'handheld-retro-emulador', 'Retro', 'Miles de títulos clásicos preinstalados.', 2199.00, NULL, 3.0, 'assets/img/console-retro.svg', 0, 20, '2026-07-10 20:03:01'),
(12, 2, 'Control Elite Series 2', 'control-elite-series-2', 'Accesorio · Xbox', 'Componentes intercambiables, grip texturizado.', 2599.00, NULL, 5.0, 'assets/img/acc-controller.svg', 0, 20, '2026-07-10 20:03:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `newsletter_suscriptores`
--
ALTER TABLE `newsletter_suscriptores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `mensajes_contacto`
--
ALTER TABLE `mensajes_contacto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `newsletter_suscriptores`
--
ALTER TABLE `newsletter_suscriptores`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
