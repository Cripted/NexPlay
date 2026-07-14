<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Validar que sea administrador
requireAdmin();

$user = getCurrentUser();
$pdo = conectarDB();

$error = '';
$success = '';

// Procesar cambio de rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['nuevo_rol'])) {
    $uid = (int)$_POST['user_id'];
    $nuevo_rol = $_POST['nuevo_rol'];
    
    // No permitir que el admin se cambie su propio rol
    if ($uid === (int)$user['id']) {
        $error = 'No puedes cambiar tu propio rol desde aquí.';
    } elseif (!in_array($nuevo_rol, ['cliente', 'admin'])) {
        $error = 'Rol no válido.';
    } else {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
                $stmt->execute([$nuevo_rol, $uid]);
                $success = 'Rol actualizado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al actualizar el rol: ' . $e->getMessage();
            }
        }
    }
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $uid = (int)$_POST['delete_user_id'];
    
    if ($uid === (int)$user['id']) {
        $error = 'No puedes eliminar tu propia cuenta desde aquí.';
    } else {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$uid]);
                $success = 'Usuario eliminado correctamente.';
            } catch (PDOException $e) {
                $error = 'Error al eliminar el usuario: ' . $e->getMessage();
            }
        }
    }
}

// Búsqueda y listado
$usuarios = [];
$search = trim($_GET['q'] ?? '');

if ($pdo) {
    try {
        if ($search) {
            $stmt = $pdo->prepare("SELECT id, nombre, correo, rol, creado_en FROM usuarios WHERE nombre LIKE ? OR correo LIKE ? ORDER BY creado_en DESC");
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->query("SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY creado_en DESC");
        }
        $usuarios = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error al consultar los usuarios: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Usuarios · NexPlay Admin</title>
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
  .rol-form {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .rol-form select {
    background: var(--bg-alt);
    border: 1px solid var(--line);
    color: var(--text);
    padding: 5px 10px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    cursor: pointer;
  }
  .badge-admin {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    font-family: var(--font-mono);
    background: rgba(124,58,237,0.2);
    color: var(--purple-soft);
    border: 1px solid var(--purple);
    letter-spacing: 0.05em;
  }
  .badge-cliente {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    font-family: var(--font-mono);
    background: rgba(34,211,238,0.1);
    color: var(--cyan);
    border: 1px solid rgba(34,211,238,0.3);
    letter-spacing: 0.05em;
  }
  .me-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    background: rgba(251,146,60,0.15);
    color: var(--orange);
    border: 1px solid rgba(251,146,60,0.3);
    margin-left: 6px;
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
        <a href="productos.php">🛒 Productos</a>
        <a href="usuarios.php" class="active">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      
      <div class="section-head" style="margin-bottom:24px;">
        <p class="eyebrow">Control de Acceso</p>
        <h2>Gestión de Usuarios</h2>
        <p>Administra los permisos y roles de todos los usuarios registrados en NexPlay.</p>
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
      <form method="GET" action="usuarios.php" class="search-row">
        <input type="text" name="q" placeholder="Buscar por nombre o correo electrónico..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem;">Buscar</button>
        <?php if ($search): ?>
          <a href="usuarios.php" class="btn btn-ghost" style="padding: 10px 20px; font-size:0.9rem; border-color:var(--pink); color:var(--pink);">Limpiar</a>
        <?php endif; ?>
      </form>

      <!-- TABLA DE USUARIOS -->
      <div class="table-panel">
        <?php if (empty($usuarios)): ?>
          <p style="color:var(--muted); text-align:center; margin:16px 0;">No se encontraron usuarios registrados.</p>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Rol Actual</th>
                  <th>Registrado</th>
                  <th style="text-align:right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($usuarios as $u): ?>
                  <?php $isMe = ((int)$u['id'] === (int)$user['id']); ?>
                  <tr>
                    <td style="font-weight:600; color:#fff;">
                      <?= htmlspecialchars($u['nombre']) ?>
                      <?php if ($isMe): ?>
                        <span class="me-badge">Tú</span>
                      <?php endif; ?>
                    </td>
                    <td style="color:var(--muted);"><?= htmlspecialchars($u['correo']) ?></td>
                    <td>
                      <?php if ($u['rol'] === 'admin'): ?>
                        <span class="badge-admin">admin</span>
                      <?php else: ?>
                        <span class="badge-cliente">cliente</span>
                      <?php endif; ?>
                    </td>
                    <td style="font-size:0.85rem; color:var(--muted-2);">
                      <?= date('d/m/Y', strtotime($u['creado_en'])) ?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                      <?php if (!$isMe): ?>
                        <!-- Formulario para cambiar rol -->
                        <form method="POST" action="usuarios.php" class="rol-form" style="margin-right:8px;">
                          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                          <select name="nuevo_rol">
                            <option value="cliente" <?= ($u['rol'] === 'cliente') ? 'selected' : '' ?>>Cliente</option>
                            <option value="admin" <?= ($u['rol'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                          </select>
                          <button type="submit" class="btn btn-ghost btn-sm" style="border-color:var(--cyan); color:var(--cyan);">Cambiar Rol</button>
                        </form>

                        <!-- Formulario para eliminar -->
                        <form method="POST" action="usuarios.php" style="display:inline;" 
                              onsubmit="return confirm('¿Eliminar definitivamente al usuario <?= htmlspecialchars(addslashes($u['nombre'])) ?>? Esta acción no se puede deshacer.');">
                          <input type="hidden" name="delete_user_id" value="<?= $u['id'] ?>">
                          <button type="submit" class="btn btn-ghost btn-sm" style="border-color:var(--pink); color:var(--pink);">Eliminar</button>
                        </form>
                      <?php else: ?>
                        <span style="color:var(--muted-2); font-size:0.8rem;">Tu cuenta activa</span>
                      <?php endif; ?>
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
