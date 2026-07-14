<?php
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$producto = obtenerProductoPorId($id);

if (!$producto) {
    header('Location: tienda.php');
    exit;
}

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
<title><?= htmlspecialchars($producto['nombre']) ?> · NexPlay</title>
<meta name="description" content="Ver detalles del producto <?= htmlspecialchars($producto['nombre']) ?> en NexPlay: especificaciones, características y compra online.">
<link rel="stylesheet" href="../css/style.css">
<style>
  .product-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 48px;
    margin-top: 32px;
  }
  .product-gallery {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    aspect-ratio: 4/3;
  }
  .product-gallery img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));
  }
  .product-info-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .product-info-panel .tag {
    align-self: flex-start;
    background: rgba(34,211,238,0.1);
    color: var(--cyan);
    border: 1px solid rgba(34,211,238,0.2);
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 16px;
  }
  .product-info-panel h1 {
    font-size: clamp(2rem, 4vw, 2.75rem);
    color: #fff;
    line-height: 1.1;
    margin-bottom: 12px;
  }
  .rating-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
  }
  .rating-row .stars {
    color: var(--cyan);
    font-size: 1.1rem;
  }
  .rating-row .score {
    color: var(--muted);
    font-size: 0.9rem;
  }
  .price-box {
    margin-bottom: 28px;
  }
  .price-box .old {
    font-size: 1.2rem;
    color: var(--muted-2);
    text-decoration: line-through;
    margin-right: 12px;
  }
  .price-box .current {
    font-size: 2.25rem;
    color: #fff;
    font-weight: 700;
    font-family: var(--font-display);
  }
  .buy-form-box {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 32px;
  }
  .stock-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: var(--muted);
    margin-bottom: 18px;
  }
  .stock-indicator .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
  }
  .stock-indicator.out-of-stock .dot {
    background: var(--pink);
  }
  .qty-selector {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
  }
  .qty-selector label {
    font-size: 0.9rem;
    color: var(--muted);
  }
  .qty-input-wrap {
    display: flex;
    align-items: center;
    border: 1px solid var(--line);
    border-radius: 8px;
    overflow: hidden;
    background: var(--bg-alt);
  }
  .qty-input-wrap button {
    background: none;
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.2s;
  }
  .qty-input-wrap button:hover {
    background: rgba(255,255,255,0.05);
  }
  .qty-input-wrap input {
    background: none;
    border: none;
    color: #fff;
    width: 48px;
    height: 36px;
    text-align: center;
    font-weight: 600;
    font-size: 0.95rem;
  }
  .qty-input-wrap input::-webkit-outer-spin-button,
  .qty-input-wrap input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  
  /* TABS SYSTEM */
  .details-tabs {
    margin-top: 64px;
    border-bottom: 1px solid var(--line);
    display: flex;
    gap: 32px;
    margin-bottom: 32px;
  }
  .tab-btn {
    background: none;
    border: none;
    color: var(--muted);
    font-size: 1.05rem;
    font-weight: 600;
    font-family: var(--font-display);
    padding: 12px 0 16px;
    cursor: pointer;
    position: relative;
    transition: color 0.2s;
  }
  .tab-btn:hover {
    color: #fff;
  }
  .tab-btn.active {
    color: var(--cyan);
  }
  .tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--cyan);
  }
  .tab-pane {
    display: none;
    animation: fadeIn 0.3s ease;
  }
  .tab-pane.active {
    display: block;
  }
  
  .specs-table {
    width: 100%;
    border-collapse: collapse;
  }
  .specs-table tr {
    border-bottom: 1px solid var(--line);
  }
  .specs-table tr:last-child {
    border-bottom: none;
  }
  .specs-table td {
    padding: 16px;
    font-size: 0.95rem;
  }
  .specs-table td.label {
    width: 30%;
    color: var(--cyan);
    font-weight: 600;
    font-family: var(--font-display);
  }
  .specs-table td.value {
    color: var(--text);
  }
  
  .features-list {
    list-style: none;
    padding: 0;
  }
  .features-list li {
    position: relative;
    padding-left: 28px;
    margin-bottom: 16px;
    font-size: 1rem;
    line-height: 1.6;
    color: var(--text);
  }
  .features-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 2px;
    color: var(--cyan);
    font-weight: 700;
    font-size: 1.1rem;
  }
  
  .others-box {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  .others-item {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 20px;
  }
  .others-item h4 {
    color: var(--cyan);
    margin-bottom: 6px;
    font-family: var(--font-display);
  }
  .others-item p {
    margin: 0;
    font-size: 0.95rem;
    color: var(--text);
  }

  .nav-cart-badge {
    background: var(--pink);
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 99px;
    position: absolute;
    top: -4px;
    right: -4px;
    border: 2px solid var(--bg-alt);
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @media (max-width: 768px) {
    .product-details-grid {
      grid-template-columns: 1fr;
      gap: 32px;
    }
    .details-tabs {
      gap: 16px;
      overflow-x: auto;
      white-space: nowrap;
    }
  }
</style>
</head>
<body>

<header class="site">
  <div class="nav-wrap">
    <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span></a>
    <nav class="main-nav">
      <a href="../index.php">Inicio</a>
      <a href="tienda.php" class="active">Tienda</a>
      <a href="blog.php">Blog y Noticias</a>
      <a href="comunidad.php">Comunidad</a>
      <a href="soporte.php">Soporte</a>
    </nav>
    <div class="nav-actions" style="display:flex; align-items:center; gap:8px;">
      <a href="tienda.php" class="icon-btn" aria-label="Ir al buscador" title="Buscar">
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

<main class="container">
  
  <div style="margin-top: 24px;">
    <a href="tienda.php" class="btn btn-ghost" style="padding: 8px 16px; border-radius: 8px; font-size: 0.85rem;">← Regresar al catálogo</a>
  </div>

  <section class="product-details-grid">
    <!-- GALERIA IMAGEN -->
    <div class="product-gallery">
      <img src="../<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
    </div>

    <!-- PANEL INFO -->
    <div class="product-info-panel">
      <span class="tag"><?= htmlspecialchars($producto['tipo']) ?></span>
      <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
      
      <div class="rating-row">
        <span class="stars"><?= renderEstrellas($producto['calificacion']) ?></span>
        <span class="score">(<?= number_format($producto['calificacion'], 1) ?> / 5.0) Calificación Verificada</span>
      </div>

      <div class="price-box">
        <?php if (!empty($producto['precio_anterior'])): ?>
          <span class="old"><?= formatoPrecio($producto['precio_anterior']) ?></span>
        <?php endif; ?>
        <span class="current"><?= formatoPrecio($producto['precio']) ?></span>
      </div>

      <div class="buy-form-box">
        <div class="stock-indicator <?= ((int)$producto['stock'] <= 0) ? 'out-of-stock' : '' ?>">
          <span class="dot"></span>
          <span>
            <?php if ((int)$producto['stock'] > 0): ?>
              <?= (int)$producto['stock'] ?> unidades disponibles en sucursal
            <?php else: ?>
              Sin existencias de momento
            <?php endif; ?>
          </span>
        </div>

        <?php if ((int)$producto['stock'] > 0): ?>
          <form action="cart_action.php" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
            
            <div class="qty-selector">
              <label for="cantidad">Cantidad:</label>
              <div class="qty-input-wrap">
                <button type="button" onclick="changeQty(-1)">-</button>
                <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?= (int)$producto['stock'] ?>" readonly>
                <button type="button" onclick="changeQty(1)">+</button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; gap:8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L22 7H6"/></svg>
              Añadir al Carrito
            </button>
          </form>
        <?php else: ?>
          <button class="btn btn-ghost" style="width:100%; border-color:var(--line); color:var(--muted); cursor:not-allowed;" disabled>No disponible</button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- PESTAÑAS DETALLES -->
  <section>
    <div class="details-tabs">
      <button class="tab-btn active" onclick="openTab(event, 'descripcion')">Descripción</button>
      <button class="tab-btn" onclick="openTab(event, 'caracteristicas')">Características</button>
      <button class="tab-btn" onclick="openTab(event, 'especificaciones')">Especificaciones</button>
      <button class="tab-btn" onclick="openTab(event, 'otros')">Otros</button>
    </div>

    <!-- PESTAÑA: DESCRIPCION -->
    <div id="descripcion" class="tab-pane active">
      <h3 style="color:#fff; margin-bottom:12px; font-family:var(--font-display);">Descripción del Producto</h3>
      <p style="font-size:1.05rem; line-height:1.7; color:var(--text); max-width: 800px;">
        <?= htmlspecialchars($producto['descripcion']) ?>. Este producto ha sido verificado bajo los más altos estándares de calidad NexPlay para garantizar el máximo rendimiento durante tus sesiones de juego.
      </p>
    </div>

    <!-- PESTAÑA: CARACTERISTICAS -->
    <div id="caracteristicas" class="tab-pane">
      <h3 style="color:#fff; margin-bottom:16px; font-family:var(--font-display);">Características Principales</h3>
      <ul class="features-list">
        <?php foreach ($producto['caracteristicas'] as $f): ?>
          <li><?= htmlspecialchars($f) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <!-- PESTAÑA: ESPECIFICACIONES -->
    <div id="especificaciones" class="tab-pane">
      <h3 style="color:#fff; margin-bottom:16px; font-family:var(--font-display);">Ficha Técnica</h3>
      <table class="specs-table">
        <tbody>
          <?php foreach ($producto['especificaciones'] as $k => $v): ?>
            <tr>
              <td class="label"><?= htmlspecialchars($k) ?></td>
              <td class="value"><?= htmlspecialchars($v) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- PESTAÑA: OTROS -->
    <div id="otros" class="tab-pane">
      <h3 style="color:#fff; margin-bottom:16px; font-family:var(--font-display);">Garantía y Adicionales</h3>
      <div class="others-box">
        <?php foreach ($producto['otros'] as $k => $v): ?>
          <div class="others-item">
            <h4><?= htmlspecialchars($k) ?></h4>
            <p><?= htmlspecialchars($v) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<footer class="site" style="margin-top: 80px;">
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
    </div>
  </div>
</footer>

<script>
function changeQty(amount) {
  const input = document.getElementById('cantidad');
  let val = parseInt(input.value) + amount;
  const min = parseInt(input.min);
  const max = parseInt(input.max);
  if (val < min) val = min;
  if (val > max) val = max;
  input.value = val;
}

function openTab(evt, tabName) {
  const tabPanes = document.getElementsByClassName('tab-pane');
  for (let i = 0; i < tabPanes.length; i++) {
    tabPanes[i].classList.remove('active');
  }
  const tabBtns = document.getElementsByClassName('tab-btn');
  for (let i = 0; i < tabBtns.length; i++) {
    tabBtns[i].classList.remove('active');
  }
  document.getElementById(tabName).classList.add('active');
  evt.currentTarget.classList.add('active');
}
</script>
<script src="../js/script.js"></script>
</body>
</html>
