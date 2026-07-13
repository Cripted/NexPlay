<?php
require_once __DIR__ . '/../includes/data.php';
$posts = obtenerBlogPosts();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog y Noticias · NexPlay</title>
<meta name="description" content="Reseñas, avances, eventos y guías de compra del mundo gamer, por el equipo editorial de NexPlay.">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="site">
  <div class="nav-wrap">
    <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span></a>
    <nav class="main-nav">
      <a href="../index.php">Inicio</a>
      <a href="tienda.php">Tienda</a>
      <a href="blog.php" class="active">Blog y Noticias</a>
      <a href="comunidad.php">Comunidad</a>
      <a href="soporte.php">Soporte</a>
    </nav>
    <div class="nav-actions">
      <a href="tienda.php" class="icon-btn" aria-label="Ir al buscador de la tienda" title="Buscar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
      </a>
      <a href="tienda.php" class="icon-btn" aria-label="Ver carrito" title="Carrito">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L22 7H6"/></svg>
      </a>
      <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<main>
  <section style="padding-bottom:0;">
    <div class="container">
      <p class="eyebrow">Contenido editorial</p>
      <h1 style="font-size:clamp(1.9rem,3.6vw,2.8rem); color:#fff; max-width:640px;">Blog y Noticias NexPlay</h1>
      <p style="max-width:560px; font-size:1.02rem;">Reseñas con veredicto real, avances de próximos lanzamientos, cobertura de eventos internacionales y guías de compra actualizadas trimestralmente.</p>
    </div>
  </section>

  <section>
    <div class="container">
      <div class="article-list">
        <?php foreach ($posts as $post): ?>
        <article class="article-card">
          <div class="media"><img src="../<?= htmlspecialchars($post['imagen']) ?>" alt="<?= htmlspecialchars($post['titulo']) ?>" loading="lazy"></div>
          <div class="body">
            <span class="<?= badgeClase($post['categoria']) ?>"><?= htmlspecialchars($post['categoria']) ?></span>
            <h3><?= htmlspecialchars($post['titulo']) ?></h3>
            <p class="excerpt"><?= htmlspecialchars($post['extracto']) ?></p>
            <div class="full">
              <p><?= htmlspecialchars($post['contenido']) ?></p>
            </div>
            <div class="row-meta">
              <span class="date"><?= fechaEs($post['fecha_publicacion']) ?></span>
              <button class="read-more">Leer más →</button>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
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
