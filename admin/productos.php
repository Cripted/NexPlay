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
            // Primero buscar el producto para ver si hay una imagen que debamos borrar
            $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $prod = $stmt->fetch();
            
            if ($prod) {
                // Si la imagen no es un placeholder de assets/img, la borramos
                if (!empty($prod['imagen']) && strpos($prod['imagen'], 'assets/img/') !== 0) {
                    $imgPath = __DIR__ . '/../' . $prod['imagen'];
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }

                $del = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                $del->execute([$id]);
                $success = "Producto eliminado de la tienda.";
            } else {
                $error = "Producto no encontrado.";
            }
        } catch (PDOException $e) {
            $error = "Error al eliminar el producto: " . $e->getMessage();
        }
    }
}

// Búsqueda y listado
$productos = [];
$search = trim($_GET['q'] ?? '');

if ($pdo) {
    try {
        if ($search) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.nombre AS categoria_nombre 
                FROM productos p 
                JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.nombre LIKE ? OR p.tipo LIKE ? OR c.nombre LIKE ?
                ORDER BY p.id DESC
            ");
            $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("
                SELECT p.*, c.nombre AS categoria_nombre 
                FROM productos p 
                JOIN categorias c ON p.categoria_id = c.id 
                ORDER BY p.id DESC
            ");
        }
        $productos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error al consultar los productos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Productos · NexPlay Admin</title>
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
    vertical-align: middle;
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
  .prod-thumb {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-sm);
    object-fit: contain;
    background: var(--bg-alt);
    border: 1px solid var(--line);
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
        <a href="articulos.php">📝 Blog / Noticias</a>
        <a href="productos.php" class="active">🛒 Productos</a>
        <a href="usuarios.php">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
        <div class="section-head" style="margin-bottom:0;">
          <p class="eyebrow">Catálogo Gamer</p>
          <h2>Productos de la Tienda</h2>
        </div>
        <a href="nuevo_producto.php" class="btn btn-orange">+ Registrar Producto</a>
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
      <form method="GET" action="productos.php" class="search-row">
        <input type="text" name="q" placeholder="Buscar por nombre, tipo (ej. Consola, Accesorio) o plataforma..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem;">Buscar</button>
        <?php if ($search): ?>
          <a href="productos.php" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem; border-color:var(--pink); color:var(--pink);">Limpiar</a>
        <?php endif; ?>
      </form>

      <!-- TABLA DE PRODUCTOS -->
      <div class="table-panel">
        <?php if (empty($productos)): ?>
          <p style="color:var(--muted); text-align:center; margin:16px 0;">No se encontraron productos en la tienda.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Imagen</th>
                  <th>Nombre</th>
                  <th>Plataforma</th>
                  <th>Tipo</th>
                  <th>Precio</th>
                  <th>Stock</th>
                  <th>Destacado</th>
                  <th style="text-align:right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($productos as $p): ?>
                  <tr>
                    <td>
                      <img src="../<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" class="prod-thumb">
                    </td>
                    <td style="font-weight:600; color:#fff;"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                    <td><span class="tag" style="background:var(--bg-alt); padding:4px 8px; border-radius:4px; font-size:0.8rem; border:1px solid var(--line);"><?= htmlspecialchars($p['tipo']) ?></span></td>
                    <td style="font-family:var(--font-mono); font-weight:600; color:var(--cyan);">$<?= number_format($p['precio'], 2) ?></td>
                    <td style="font-family:var(--font-mono);"><?= $p['stock'] ?></td>
                    <td>
                      <?= ($p['destacado']) ? '<span style="color:var(--yellow);">★ Sí</span>' : '<span style="color:var(--muted-2);">No</span>' ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                      <a href="editar_producto.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm" style="margin-right:8px; border-color:var(--cyan); color:var(--cyan);">Editar</a>
                      
                      <form method="POST" action="productos.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                        <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
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
