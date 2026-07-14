<?php
require_once __DIR__ . '/../includes/auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0;

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    $res = register($nombre, $correo, $password, $confirmar);
    if ($res['success']) {
        $success = $res['message'];
        // Limpiar campos
        $nombre = $correo = '';
    } else {
        $error = $res['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro · NexPlay</title>
<meta name="description" content="Regístrate en NexPlay para unirte a la mejor comunidad gamer de México.">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<header class="site">
  <div class="nav-wrap">
    <a href="../index.php" class="logo"><span class="dot"></span>Nex<span>Play</span></a>
    <nav class="main-nav">
      <a href="../index.php">Inicio</a>
      <a href="tienda.php">Tienda</a>
      <a href="blog.php">Blog y Noticias</a>
      <a href="comunidad.php">Comunidad</a>
      <a href="soporte.php">Soporte</a>
    </nav>
    <div class="nav-actions">
      <a href="tienda.php" class="icon-btn" aria-label="Ir al buscador de la tienda" title="Buscar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
      </a>
      <a href="carrito.php" class="icon-btn" aria-label="Ver carrito" title="Carrito" style="position:relative;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L22 7H6"/></svg>
        <?php if ($cartCount > 0): ?>
          <span class="nav-cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <button class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
    </div>
  </div>
</header>

<main>
  <section style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="container" style="max-width: 480px; width: 100%;">
      
      <div class="section-head" style="text-align: center; margin-bottom: 24px; max-width: 100%;">
        <p class="eyebrow">Únete a la partida</p>
        <h2>Crea tu cuenta</h2>
        <p>Regístrate gratis y obtén acceso a los beneficios de la comunidad NexPlay.</p>
      </div>

      <div class="form-panel" style="background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 32px; box-shadow: 0 8px 32px rgba(0,0,0,0.4);">
        
        <?php if (!empty($error)): ?>
          <div style="border: 1px solid var(--pink); color: var(--pink); padding: 12px; background: rgba(244,114,182,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
            ⚠️ <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div style="border: 1px solid var(--cyan); color: var(--cyan); padding: 12px; background: rgba(34,211,238,0.08); border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.9rem;">
            ✓ <?= htmlspecialchars($success) ?>
            <div style="margin-top: 10px;">
              <a href="login.php" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; color: #0a0716;">Ir al Login →</a>
            </div>
          </div>
        <?php endif; ?>

        <form method="POST" action="registro.php">
          <div class="form-row">
            <label for="nombre">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej. Juan Pérez" value="<?= htmlspecialchars($nombre ?? '') ?>">
          </div>

          <div class="form-row">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" required placeholder="tu@correo.com" value="<?= htmlspecialchars($correo ?? '') ?>">
          </div>
          
          <div class="form-row">
            <label for="password">Contraseña (mínimo 6 caracteres)</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
          </div>

          <div class="form-row">
            <label for="confirmar">Confirmar contraseña</label>
            <input type="password" id="confirmar" name="confirmar" required placeholder="••••••••">
          </div>

          <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Crear cuenta</button>
        </form>

        <div style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: var(--muted);">
          ¿Ya tienes una cuenta? <a href="login.php" style="color: var(--cyan); font-weight: 600;">Inicia sesión aquí</a>
        </div>
      </div>

    </div>
  </section>
</main>

<footer class="site">
  <div class="container">
    <div class="footer-bottom" style="border-top: 1px solid var(--line); padding-top: 24px; text-align: center;">
      <span>© 2026 NexPlay · IEU · Sistemas de Administración de Contenidos</span>
    </div>
  </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
