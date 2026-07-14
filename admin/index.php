<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Validar que sea administrador
requireAdmin();

$user = getCurrentUser();
$pdo = conectarDB();

// Obtener estadísticas
$stats = [
    'productos' => 0,
    'articulos' => 0,
    'usuarios' => 0,
    'mensajes' => 0
];

if ($pdo) {
    try {
        $stats['productos'] = (int)$pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
        $stats['articulos'] = (int)$pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
        $stats['usuarios'] = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $stats['mensajes'] = (int)$pdo->query("SELECT COUNT(*) FROM mensajes_contacto")->fetchColumn();
    } catch (PDOException $e) {
        error_log('Admin stats error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Administración · NexPlay</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  .admin-grid {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 32px;
    margin-top: 32px;
  }
  .admin-sidebar {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
    height: fit-content;
  }
  .admin-menu a {
    display: block;
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    color: var(--muted);
    font-weight: 500;
    margin-bottom: 8px;
    transition: all 0.2s ease;
  }
  .admin-menu a:hover, .admin-menu a.active {
    background: var(--panel-2);
    color: var(--cyan);
    border-left: 3px solid var(--cyan);
  }
  .admin-card {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
    text-align: center;
    transition: transform 0.2s ease;
  }
  .admin-card:hover {
    transform: translateY(-4px);
    border-color: var(--purple-soft);
  }
  .admin-card h3 {
    font-size: 2.5rem;
    color: var(--cyan);
    margin: 0;
    font-family: var(--font-mono);
  }
  .admin-card p {
    margin: 8px 0 0;
    font-size: 0.9rem;
    color: var(--text);
  }
  .table-panel {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
    margin-top: 32px;
  }
  table.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
  }
  table.admin-table th, table.admin-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--line);
  }
  table.admin-table th {
    color: var(--cyan);
    font-family: var(--font-display);
    font-weight: 600;
  }
  table.admin-table td {
    color: var(--text);
    font-size: 0.95rem;
  }
  .btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
    border-radius: 6px;
    transform: none !important;
  }
  @media (max-width: 768px) {
    .admin-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>
<body>

<header class="site">
  <div class="nav-wrap">
    <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span> <small style="font-size:0.8rem; color:var(--pink); font-family:var(--font-mono);">ADMIN</small></a>
    <div style="display:flex; align-items:center; gap:16px;">
      <span style="font-size:0.9rem; color:var(--cyan);">👤 <?= htmlspecialchars($user['nombre']) ?></span>
      <a href="../index.php" class="btn btn-ghost btn-sm">Ver Sitio</a>
      <a href="../pages/logout.php" class="btn btn-ghost btn-sm" style="border-color:var(--pink); color:var(--pink);">Salir</a>
    </div>
  </div>
</header>

<main class="container">
  <div class="admin-grid">
    
    <!-- MENU LATERAL -->
    <aside class="admin-sidebar">
      <nav class="admin-menu">
        <a href="index.php" class="active">📊 Dashboard</a>
        <a href="articulos.php">📝 Blog / Noticias</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="usuarios.php">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      <div class="section-head">
        <p class="eyebrow">Resumen de control</p>
        <h2>Panel de Control</h2>
        <p>Gestiona los contenidos del blog, los productos del catálogo y los roles de usuario.</p>
      </div>

      <!-- CARDS DE ESTADISTICAS -->
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
        <div class="admin-card">
          <h3><?= $stats['productos'] ?></h3>
          <p>Productos en tienda</p>
        </div>
        <div class="admin-card">
          <h3><?= $stats['articulos'] ?></h3>
          <p>Artículos en blog</p>
        </div>
        <div class="admin-card">
          <h3><?= $stats['usuarios'] ?></h3>
          <p>Usuarios registrados</p>
        </div>
        <div class="admin-card">
          <h3><?= $stats['mensajes'] ?></h3>
          <p>Mensajes de soporte</p>
        </div>
      </div>

      <!-- ACCIONES RAPIDAS -->
      <div class="table-panel">
        <h3 style="color:#fff; border-bottom: 1px solid var(--line); padding-bottom: 12px; margin-bottom: 16px;">Acciones Rápidas</h3>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
          <a href="nuevo_articulo.php" class="btn btn-primary">+ Redactar Artículo</a>
          <a href="nuevo_producto.php" class="btn btn-orange">+ Registrar Producto</a>
          <a href="usuarios.php" class="btn btn-ghost">Gestionar Permisos de Usuarios</a>
        </div>
      </div>

    </section>

  </div>
</main>

<footer class="site" style="margin-top: 64px;">
  <div class="container">
    <div class="footer-bottom" style="border-top: 1px solid var(--line); padding-top: 24px; text-align: center;">
      <span>© 2026 NexPlay Admin · IEU · Sistemas de Administración de Contenidos</span>
    </div>
  </div>
</footer>

</body>
</html>
