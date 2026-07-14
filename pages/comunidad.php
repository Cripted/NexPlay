<?php
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth.php';
$comunidad = obtenerComunidad();
$stats = $comunidad['stats'];
$contribuidores = $comunidad['contribuidores'];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comunidad · NexPlay</title>
<meta name="description" content="La comunidad gamer de NexPlay: Discord, torneos esports, ranking de reseñadores y estadísticas en vivo.">
<link rel="stylesheet" href="../css/style.css">
<style>
@media (max-width: 576px) {
  .nav-user-span { display: none !important; }
}
</style>
</head>
<body>

<header class="site">
  <div class="nav-wrap">
    <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span></a>
    <nav class="main-nav">
      <a href="../index.php">Inicio</a>
      <a href="tienda.php">Tienda</a>
      <a href="blog.php">Blog y Noticias</a>
      <a href="comunidad.php" class="active">Comunidad</a>
      <a href="soporte.php">Soporte</a>
    </nav>
    <div class="nav-actions" style="display:flex; align-items:center; gap:8px;">
      <a href="tienda.php" class="icon-btn" aria-label="Ir al buscador de la tienda" title="Buscar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
      </a>
      <a href="carrito.php" class="icon-btn" aria-label="Ver carrito" title="Carrito" style="position:relative;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L22 7H6"/></svg>
        <?php if ($cartCount > 0): ?>
          <span class="nav-cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <?php if (isLoggedIn()): ?>
        <?php $u = getCurrentUser(); ?>
        <span class="nav-user-span" style="font-size:0.85rem; color:var(--cyan); display:inline-flex; align-items:center; gap:4px; font-weight: 500;">
          👤 <span style="max-width:70px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($u['nombre']) ?></span>
        </span>
        <?php if ($u['rol'] === 'admin'): ?>
          <a href="../admin/index.php" class="btn btn-ghost" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-display); transform: none;">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-ghost" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 6px; border-color: var(--pink); color: var(--pink); font-family: var(--font-display); transform: none;">Salir</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary" style="padding: 7px 14px; font-size: 0.75rem; border-radius: 6px; font-family: var(--font-display); color:#0a0716; transform: none;">Acceder</a>
      <?php endif; ?>
      <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<main>

  <!-- ENCABEZADO -->
  <section style="padding-bottom:0;">
    <div class="container">
      <p class="eyebrow">Comunidad gamer mexicana</p>
      <h1 style="font-size:clamp(1.9rem,3.6vw,2.8rem); color:#fff; max-width:640px;">La comunidad NexPlay</h1>
      <p style="max-width:560px; font-size:1.02rem;">Discord, torneos por plataforma, wikis colaborativas y un ranking de reseñadores que ayuda a miles de jugadores a decidir mejor cada compra.</p>
    </div>
  </section>

  <!-- GRID DE ACCIONES -->
  <section>
    <div class="container">
      <div class="section-head">
        <p class="eyebrow">Participa</p>
        <h2>Tres formas de sumarte</h2>
        <p>Elige cómo quieres formar parte: chatea con otros jugadores, compite por crédito de tienda o comparte tu propia experiencia con el catálogo.</p>
      </div>
      <div class="community-grid">
        <div class="community-card">
          <div class="community-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <h3>Únete a Discord</h3>
          <p>Chatea en tiempo real, encuentra squad para tus partidas y entérate primero de los drops y preventas de la tienda.</p>
          <a href="#" class="read-more">Unirse al servidor →</a>
        </div>
        <div class="community-card">
          <div class="community-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0z"/><path d="M7 5H3v2a4 4 0 0 0 4 4M17 5h4v2a4 4 0 0 1-4 4"/></svg>
          </div>
          <h3>Torneos &amp; esports</h3>
          <p>Torneos mensuales por plataforma con crédito de tienda como premio e insignias exclusivas de temporada en tu perfil.</p>
          <a href="#" class="read-more">Ver calendario →</a>
        </div>
        <div class="community-card">
          <div class="community-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
          </div>
          <h3>Comparte tu experiencia</h3>
          <p>Publica reseñas verificadas por compra, gana puntos de reputación y súmate al ranking de Top Reseñadores del mes.</p>
          <a href="#contribuir" class="read-more">Escribir reseña →</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ESTADÍSTICAS -->
  <section>
    <div class="container">
      <div class="section-head">
        <p class="eyebrow">Módulo · Estadísticas en vivo</p>
        <h2>La comunidad en números</h2>
        <p>Actividad agregada de foros, reseñas e insignias otorgadas dentro de la plataforma.</p>
      </div>
      <div class="kpi-strip">
        <div class="kpi"><strong><?= number_format((int)$stats['miembros_activos']) ?></strong><span>Miembros activos</span></div>
        <div class="kpi"><strong><?= number_format((int)$stats['resenas_publicadas']) ?></strong><span>Reseñas publicadas</span></div>
        <div class="kpi"><strong><?= number_format((int)$stats['discusiones_activas']) ?></strong><span>Discusiones activas</span></div>
        <div class="kpi"><strong><?= number_format((int)$stats['insignias_otorgadas']) ?></strong><span>Insignias otorgadas</span></div>
      </div>
    </div>
  </section>

  <!-- TOP CONTRIBUIDORES -->
  <section>
    <div class="container">
      <div class="section-head">
        <p class="eyebrow">Módulo · Ranking</p>
        <h2>Top reseñadores del mes</h2>
        <p>Los perfiles con más reseñas verificadas y likes de la comunidad este mes.</p>
      </div>
      <div class="contributors-list">
        <?php foreach ($contribuidores as $i => $c): ?>
        <div class="contributor">
          <div class="contributor-rank"><?= $i + 1 ?></div>
          <div class="contributor-info">
            <strong><?= htmlspecialchars($c['nombre']) ?></strong>
            <p><?= (int)$c['total_resenas'] ?> reseñas · <?= (int)$c['total_likes'] ?> likes totales</p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- FORMULARIO: COMPARTE TU EXPERIENCIA -->
  <section id="contribuir">
    <div class="container">
      <div class="section-head">
        <p class="eyebrow">Módulo · Nueva reseña</p>
        <h2>Comparte tu experiencia</h2>
        <p>Cuéntale a la comunidad qué te pareció un producto de nuestro catálogo. Las reseñas verificadas suman puntos hacia el ranking mensual.</p>
      </div>

      <div class="form-panel" style="max-width:640px;">
        <form id="review-form" novalidate>
          <div class="two-col">
            <div class="form-row">
              <label for="review-nombre">Tu nombre o alias</label>
              <input type="text" id="review-nombre" name="review-nombre" placeholder="Ej. RonarX_MX">
              <span class="error-msg">Ingresa tu nombre o alias.</span>
            </div>
            <div class="form-row">
              <label for="review-rating">Calificación</label>
              <select id="review-rating" name="review-rating">
                <option value="">Selecciona</option>
                <option value="5">★★★★★ Excelente</option>
                <option value="4">★★★★☆ Muy bueno</option>
                <option value="3">★★★☆☆ Bueno</option>
                <option value="2">★★☆☆☆ Regular</option>
                <option value="1">★☆☆☆☆ Malo</option>
              </select>
              <span class="error-msg">Selecciona una calificación.</span>
            </div>
          </div>
          <div class="form-row">
            <label for="review-titulo">Producto reseñado</label>
            <input type="text" id="review-titulo" name="review-titulo" placeholder="Ej. PlayStation 5 Slim">
            <span class="error-msg">Selecciona el título que quieres reseñar.</span>
          </div>
          <div class="form-row">
            <label for="review-texto">Tu reseña</label>
            <textarea id="review-texto" name="review-texto" rows="5" placeholder="Cuéntanos tu experiencia de compra y uso…"></textarea>
            <span class="error-msg">Cuéntanos un poco más (mínimo 15 caracteres).</span>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Publicar reseña</button>
        </form>

        <div class="form-success" id="review-success">
          <div class="tick">✓</div>
          <h3 style="color:#fff;">¡Gracias por tu reseña!</h3>
          <p>Tu publicación entrará a revisión de verificación de compra y sumará puntos hacia el ranking mensual.</p>
          <button class="btn btn-ghost" id="review-reset-btn">Escribir otra reseña</button>
        </div>
      </div>
    </div>
  </section>

  <!-- PROMO -->
  <section>
    <div class="container">
      <div class="promo">
        <div>
          <span class="badge cyan">Recompensas</span>
          <h3>Insignias y crédito de tienda</h3>
          <p>Cada reseña verificada, hilo respondido o contribución a una wiki suma progreso hacia insignias de perfil y crédito canjeable en el catálogo.</p>
        </div>
        <a href="soporte.php" class="btn btn-orange">Ver cómo funciona →</a>
      </div>
    </div>
  </section>

</main>

<footer class="site">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span></a>
        <p style="margin-top:14px; max-width:280px;">Tienda de videojuegos, consolas y accesorios con contenido editorial y comunidad gamer mexicana.</p>
      </div>
      <div>
        <h4>Explorar</h4>
        <ul>
          <li><a href="../index.php">Inicio</a></li>
          <li><a href="tienda.php">Tienda</a></li>
          <li><a href="blog.php">Blog y Noticias</a></li>
          <li><a href="comunidad.php">Comunidad</a></li>
          <li><a href="soporte.php">Soporte</a></li>
        </ul>
      </div>
      <div>
        <h4>Plataformas</h4>
        <ul>
          <li><a href="tienda.php">PlayStation 5</a></li>
          <li><a href="tienda.php">Xbox Series X|S</a></li>
          <li><a href="tienda.php">Nintendo Switch</a></li>
          <li><a href="tienda.php">Retrogaming</a></li>
        </ul>
      </div>
      <div>
        <h4>Newsletter</h4>
        <p>Recibe ofertas y noticias del mundo gamer cada semana.</p>
        <form onsubmit="return false;" style="display:flex; gap:8px;">
          <input type="email" placeholder="tu@correo.com" style="flex:1; background:var(--bg-alt); border:1px solid var(--line); border-radius:8px; padding:10px; color:var(--text);">
          <button class="btn btn-primary" style="padding:10px 16px;">→</button>
        </form>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 NexPlay · IEU · Sistemas de Administración de Contenidos</span>
      <div class="footer-legal">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">LFPDPPP</a>
      </div>
    </div>
  </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
