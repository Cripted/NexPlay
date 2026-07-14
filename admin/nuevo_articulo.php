<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Validar que sea administrador
requireAdmin();

$user = getCurrentUser();
$pdo = conectarDB();

$error = '';
$success = '';

function generateSlug($titulo) {
    $t = mb_strtolower($titulo, 'UTF-8');
    $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    $t = preg_replace('/[^a-z0-9\s-]/', '', $t);
    $t = preg_replace('/[\s-]+/', '-', $t);
    return trim($t, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $extracto = trim($_POST['extracto'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');
    $fecha_publicacion = trim($_POST['fecha_publicacion'] ?? '');
    $imagen_url = trim($_POST['imagen_url'] ?? '');
    
    if (empty($titulo) || empty($categoria) || empty($extracto) || empty($contenido) || empty($fecha_publicacion)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $imagen = 'assets/img/article-resena.svg'; // Default placeholder
        
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
                $filename = 'blog_' . uniqid() . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    $imagen = 'uploads/' . $filename;
                } else {
                    $error = 'Error al guardar el archivo subido.';
                }
            } else {
                $error = 'Tipo de archivo de imagen no permitido.';
            }
        } elseif (!empty($imagen_url)) {
            $imagen = $imagen_url;
        }

        if (empty($error)) {
            $slug = generateSlug($titulo);
            
            if ($pdo) {
                try {
                    // Verificar si ya existe el slug
                    $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
                    $check->execute([$slug]);
                    if ($check->fetch()) {
                        // Modificar el slug para evitar colisión
                        $slug .= '-' . rand(100, 999);
                    }

                    $stmt = $pdo->prepare("INSERT INTO blog_posts (titulo, slug, categoria, extracto, contenido, imagen, fecha_publicacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$titulo, $slug, $categoria, $extracto, $contenido, $imagen, $fecha_publicacion]);
                    
                    header('Location: articulos.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Error al registrar en la base de datos: ' . $e->getMessage();
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
<title>Nuevo Artículo · NexPlay Admin</title>
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
        <a href="articulos.php" class="active">📝 Blog / Noticias</a>
        <a href="productos.php">🛒 Productos</a>
        <a href="usuarios.php">👥 Usuarios</a>
      </nav>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <section style="padding:0;">
      
      <div class="section-head" style="margin-bottom:24px;">
        <p class="eyebrow">Redactar</p>
        <h2>Nuevo Artículo</h2>
        <p>Escribe y publica una nueva entrada para el blog oficial de NexPlay.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div style="border: 1px solid var(--pink); color: var(--pink); padding: 12px; background: rgba(244,114,182,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
          ⚠️ <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="form-panel">
        <form method="POST" action="nuevo_articulo.php" enctype="multipart/form-data">
          
          <div class="form-row">
            <label for="titulo">Título del artículo *</label>
            <input type="text" id="titulo" name="titulo" required placeholder="Ej. Los mejores lanzamientos de consolas en 2026" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
          </div>

          <div class="two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
            <div class="form-row">
              <label for="categoria">Categoría *</label>
              <select id="categoria" name="categoria" required>
                <option value="">Seleccionar...</option>
                <option value="Noticia" <?= (($_POST['categoria'] ?? '') === 'Noticia') ? 'selected' : '' ?>>Noticia</option>
                <option value="Artículo" <?= (($_POST['categoria'] ?? '') === 'Artículo') ? 'selected' : '' ?>>Artículo</option>
                <option value="Reseña" <?= (($_POST['categoria'] ?? '') === 'Reseña') ? 'selected' : '' ?>>Reseña</option>
                <option value="Guía" <?= (($_POST['categoria'] ?? '') === 'Guía') ? 'selected' : '' ?>>Guía</option>
                <option value="Avance" <?= (($_POST['categoria'] ?? '') === 'Avance') ? 'selected' : '' ?>>Avance</option>
                <option value="Evento" <?= (($_POST['categoria'] ?? '') === 'Evento') ? 'selected' : '' ?>>Evento</option>
                <option value="Comunidad" <?= (($_POST['categoria'] ?? '') === 'Comunidad') ? 'selected' : '' ?>>Comunidad</option>
                <option value="Esports" <?= (($_POST['categoria'] ?? '') === 'Esports') ? 'selected' : '' ?>>Esports</option>
              </select>
            </div>
            
            <div class="form-row">
              <label for="fecha_publicacion">Fecha de Publicación *</label>
              <input type="date" id="fecha_publicacion" name="fecha_publicacion" required value="<?= htmlspecialchars($_POST['fecha_publicacion'] ?? date('Y-m-d')) ?>">
            </div>
          </div>

          <div class="form-row">
            <label for="extracto">Extracto (Resumen corto para portada) *</label>
            <input type="text" id="extracto" name="extracto" required placeholder="Resumen corto de 1 o 2 renglones" value="<?= htmlspecialchars($_POST['extracto'] ?? '') ?>">
          </div>

          <div class="form-row">
            <label for="contenido">Contenido del Artículo *</label>
            <textarea id="contenido" name="contenido" rows="12" required placeholder="Escribe el cuerpo completo del artículo aquí..." style="font-family:inherit; resize:vertical;"><?= htmlspecialchars($_POST['contenido'] ?? '') ?></textarea>
          </div>

          <div style="border-top:1px solid var(--line); margin: 24px 0; padding-top: 24px;">
            <h4 style="color:#fff; margin-bottom:12px;">Imagen Destacada</h4>
            
            <div class="two-col" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
              <div class="form-row">
                <label for="imagen_archivo">Subir archivo de imagen</label>
                <input type="file" id="imagen_archivo" name="imagen_archivo" accept="image/*">
              </div>
              
              <div class="form-row">
                <label for="imagen_url">O ingresar URL de imagen externa</label>
                <input type="text" id="imagen_url" name="imagen_url" placeholder="https://ejemplo.com/imagen.jpg" value="<?= htmlspecialchars($_POST['imagen_url'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div style="display:flex; gap:16px; justify-content:flex-end;">
            <a href="articulos.php" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary">Publicar Entrada</button>
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
