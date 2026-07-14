<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Validar que sea administrador
requireAdmin();

$user = getCurrentUser();
$pdo = conectarDB();

$error = '';
$success = '';

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    if ($pdo) {
        try {
            // Primero buscar el post para ver si hay una imagen que debamos borrar
            $stmt = $pdo->prepare("SELECT imagen FROM blog_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
            if ($post) {
                // Si la imagen no es un placeholder predeterminado de assets/img, podemos borrarla
                if (!empty($post['imagen']) && strpos($post['imagen'], 'assets/img/') !== 0) {
                    $imgPath = __DIR__ . '/../' . $post['imagen'];
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }

                $del = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                $del->execute([$id]);
                $success = "Artículo eliminado correctamente.";
            } else {
                $error = "Artículo no encontrado.";
            }
        } catch (PDOException $e) {
            $error = "Error al eliminar el artículo: " . $e->getMessage();
        }
    }
}

// Búsqueda y listado
$articulos = [];
$search = trim($_GET['q'] ?? '');

if ($pdo) {
    try {
        if ($search) {
            $stmt = $pdo->prepare("SELECT id, titulo, slug, categoria, extracto, fecha_publicacion FROM blog_posts WHERE titulo LIKE ? OR categoria LIKE ? ORDER BY fecha_publicacion DESC");
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT id, titulo, slug, categoria, extracto, fecha_publicacion FROM blog_posts ORDER BY fecha_publicacion DESC");
        }
        $articulos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error al consultar los artículos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Blog · NexPlay Admin</title>
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
  .table-panel {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
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
  .search-row {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
  }
  .search-row input {
    flex: 1;
    background: var(--panel);
    border: 1px solid var(--line);
    padding: 10px 16px;
    border-radius: var(--radius-sm);
    color: #fff;
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
        <a href="index.php">📊 Dashboard</a>
        <a href="articulos.php" class="active">📝 Blog / Noticias</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="usuarios.php">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div class="section-head" style="margin-bottom:0;">
          <p class="eyebrow">Publicaciones del Blog</p>
          <h2>Blog &amp; Noticias</h2>
        </div>
        <a href="nuevo_articulo.php" class="btn btn-primary">+ Nuevo Artículo</a>
      </div>

      <?php if (!empty($success)): ?>
        <div style="border: 1px solid var(--cyan); color: var(--cyan); padding: 12px; background: rgba(34,211,238,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
          ✓ <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div style="border: 1px solid var(--pink); color: var(--pink); padding: 12px; background: rgba(244,114,182,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
          ⚠️ <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- BUSCADOR -->
      <form method="GET" action="articulos.php" class="search-row">
        <input type="text" name="q" placeholder="Buscar por título o categoría..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem;">Buscar</button>
        <?php if ($search): ?>
          <a href="articulos.php" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem; border-color:var(--pink); color:var(--pink);">Limpiar</a>
        <?php endif; ?>
      </form>

      <!-- TABLA DE ARTICULOS -->
      <div class="table-panel">
        <?php if (empty($articulos)): ?>
          <p style="color:var(--muted); text-align:center; margin:16px 0;">No se encontraron artículos publicados.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Categoría</th>
                  <th>Fecha Publicación</th>
                  <th style="text-align:right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($articulos as $art): ?>
                  <tr>
                    <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($art['titulo']) ?></td>
                    <td><span class="tag" style="background:var(--bg-alt); padding:4px 8px; border-radius:4px; font-size:0.8rem; border:1px solid var(--line);"><?= htmlspecialchars($art['categoria']) ?></span></td>
                    <td><?= date('d/m/Y', strtotime($art['fecha_publicacion'])) ?></td>
                    <td style="text-align:right; white-space:nowrap;">
                      <a href="editar_articulo.php?id=<?= $art['id'] ?>" class="btn btn-ghost btn-sm" style="margin-right:8px; border-color:var(--cyan); color:var(--cyan);">Editar</a>
                      
                      <form method="POST" action="articulos.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="delete_id" value="<?= $art['id'] ?>">
                        <button type="submit" class="btn btn-ghost btn-sm" style="border-color:var(--pink); color:var(--pink);">Eliminar</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
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
