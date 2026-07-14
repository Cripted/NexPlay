<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Validar que sea administrador
requireAdmin();

$user = getCurrentUser();
$pdo = conectarDB();

$error = '';
$success = '';

// Obtener categorías para el select
$categorias = [];
if ($pdo) {
    try {
        $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY id ASC")->fetchAll();
    } catch (PDOException $e) {
        $error = "Error al obtener categorías: " . $e->getMessage();
    }
}

function generateSlug($titulo) {
    $t = mb_strtolower($titulo, 'UTF-8');
    $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    $t = preg_replace('/[^a-z0-9\s-]/', '', $t);
    $t = preg_replace('/[\s-]+/', '-', $t);
    return trim($t, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $tipo = trim($_POST['tipo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $precio_anterior = !empty($_POST['precio_anterior']) ? (float)$_POST['precio_anterior'] : null;
    $calificacion = (float)($_POST['calificacion'] ?? 5.0);
    $stock = (int)($_POST['stock'] ?? 20);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $imagen_url = trim($_POST['imagen_url'] ?? '');

    if (empty($nombre) || $categoria_id <= 0 || empty($tipo) || empty($descripcion) || $precio <= 0) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $imagen = 'assets/img/console-ps.svg'; // Default placeholder
        
        // Manejar subida de archivo si existe
        if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen_archivo'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'prod_' . uniqid() . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imagen = 'uploads/' . $filename;
                } else {
                    $error = 'Error al guardar el archivo de imagen subido.';
                }
            } else {
                $error = 'Tipo de archivo de imagen no permitido.';
            }
        } elseif (!empty($imagen_url)) {
            $imagen = $imagen_url;
        }

        if (empty($error)) {
            $slug = generateSlug($nombre);
            
            if ($pdo) {
                try {
                    // Verificar si ya existe el slug
                    $check = $pdo->prepare("SELECT id FROM productos WHERE slug = ?");
                    $check->execute([$slug]);
                    if ($check->fetch()) {
                        $slug .= '-' . rand(100, 999);
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO productos 
                        (categoria_id, nombre, slug, tipo, descripcion, precio, precio_anterior, calificacion, imagen, destacado, stock) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$categoria_id, $nombre, $slug, $tipo, $descripcion, $precio, $precio_anterior, $calificacion, $imagen, $destacado, $stock]);
                    
                    header('Location: productos.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Error al registrar el producto: ' . $e->getMessage();
                }
            } else {
                $error = 'No hay conexión con la base de datos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Producto · NexPlay Admin</title>
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
  .form-panel {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 32px;
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
        <a href="index.php">📊 Dashboard</a>
        <a href="articulos.php">📝 Blog / Noticias</a>
        <a href="productos.php" class="active">🛒 Productos</a>
        <a href="usuarios.php">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      
      <div class="section-head" style="margin-bottom:24px;">
        <p class="eyebrow">Añadir a la Tienda</p>
        <h2>Registrar Producto</h2>
        <p>Añade un nuevo juego, consola o accesorio al catálogo general de NexPlay.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div style="border: 1px solid var(--pink); color: var(--pink); padding: 12px; background: rgba(244,114,182,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
          ⚠️ <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="form-panel">
        <form method="POST" action="nuevo_producto.php" enctype="multipart/form-data">
          
          <div class="form-row">
            <label for="nombre">Nombre del producto *</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Control Inalámbrico Xbox - Sky Cipher" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
          </div>

          <div class="two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="form-row">
              <label for="categoria_id">Plataforma / Categoría *</label>
              <select id="categoria_id" name="categoria_id" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= ((int)($_POST['categoria_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-row">
              <label for="tipo">Tipo de Producto *</label>
              <select id="tipo" name="tipo" required>
                <option value="">Seleccionar...</option>
                <option value="Consola" <?= (($_POST['tipo'] ?? '') === 'Consola') ? 'selected' : '' ?>>Consola</option>
                <option value="Accesorio" <?= (($_POST['tipo'] ?? '') === 'Accesorio') ? 'selected' : '' ?>>Accesorio</option>
                <option value="Videojuego" <?= (($_POST['tipo'] ?? '') === 'Videojuego') ? 'selected' : '' ?>>Videojuego</option>
                <option value="Bundle" <?= (($_POST['tipo'] ?? '') === 'Bundle') ? 'selected' : '' ?>>Bundle</option>
                <option value="Retro" <?= (($_POST['tipo'] ?? '') === 'Retro') ? 'selected' : '' ?>>Retro</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <label for="descripcion">Descripción *</label>
            <input type="text" id="descripcion" name="descripcion" required placeholder="Ficha técnica corta para la tarjeta (ej. 1TB SSD, 4K nativo)" value="<?= htmlspecialchars($_POST['descripcion'] ?? '') ?>">
          </div>

          <div class="three-col" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px;">
            <div class="form-row">
              <label for="precio">Precio MXN *</label>
              <input type="number" step="0.01" id="precio" name="precio" required placeholder="1299.00" value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
            </div>
            
            <div class="form-row">
              <label for="precio_anterior">Precio Anterior (Descuento)</label>
              <input type="number" step="0.01" id="precio_anterior" name="precio_anterior" placeholder="Opcional" value="<?= htmlspecialchars($_POST['precio_anterior'] ?? '') ?>">
            </div>

            <div class="form-row">
              <label for="stock">Stock disponible *</label>
              <input type="number" id="stock" name="stock" required placeholder="20" value="<?= htmlspecialchars($_POST['stock'] ?? '20') ?>">
            </div>
          </div>

          <div class="two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items: center;">
            <div class="form-row">
              <label for="calificacion">Calificación Inicial (0.0 a 5.0)</label>
              <input type="number" step="0.1" min="0" max="5" id="calificacion" name="calificacion" required placeholder="5.0" value="<?= htmlspecialchars($_POST['calificacion'] ?? '5.0') ?>">
            </div>

            <div class="form-row" style="display:flex; align-items:center; gap:8px; margin-top: 18px;">
              <input type="checkbox" id="destacado" name="destacado" style="width:auto; margin:0;" <?= isset($_POST['destacado']) ? 'checked' : '' ?>>
              <label for="destacado" style="margin:0; cursor:pointer;">Marcar como destacado (Se muestra en Inicio)</label>
            </div>
          </div>

          <div style="border-top:1px solid var(--line); margin: 24px 0; padding-top: 24px;">
            <h4 style="color:#fff; margin-bottom:12px;">Imagen del Producto</h4>
            
            <div class="two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
              <div class="form-row">
                <label for="imagen_archivo">Subir archivo de imagen</label>
                <input type="file" id="imagen_archivo" name="imagen_archivo" accept="image/*">
              </div>
              
              <div class="form-row">
                <label for="imagen_url">O ingresar URL de imagen existente</label>
                <input type="text" id="imagen_url" name="imagen_url" placeholder="assets/img/console-ps.svg" value="<?= htmlspecialchars($_POST['imagen_url'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div style="display:flex; gap:16px; justify-content:flex-end;">
            <a href="productos.php" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar Producto</button>
          </div>

        </form>
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
