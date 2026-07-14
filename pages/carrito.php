<?php
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Endpoint AJAX para registrar la compra tras la simulación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_checkout'])) {
    header('Content-Type: application/json');
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comprar.']);
        exit;
    }

    $carrito = $_SESSION['carrito'] ?? [];
    if (empty($carrito)) {
        echo json_encode(['success' => false, 'message' => 'El carrito está vacío.']);
        exit;
    }

    $user = getCurrentUser();
    $pdo = conectarDB();
    if ($pdo) {
        // Verificar si el usuario realmente existe en la base de datos (evita sesiones caducas)
        try {
            $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmtUser->execute([$user['id']]);
            if (!$stmtUser->fetch()) {
                // Destruir sesión obsoleta
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
                echo json_encode(['success' => false, 'message' => 'Tu sesión ha caducado o tu usuario fue eliminado de la base de datos. Por favor, inicia sesión o regístrate de nuevo.']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al validar usuario: ' . $e->getMessage()]);
            exit;
        }

        try {
            $pdo->beginTransaction();
            
            // Calcular total
            $total = 0;
            foreach ($carrito as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }
            
            // Insertar en pedidos
            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, ?, 'pagado')");
            $stmt->execute([$user['id'], $total]);
            $pedido_id = $pdo->lastInsertId();
            
            // Insertar items y actualizar stock
            $stmtItem = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            foreach ($carrito as $item) {
                $stmtItem->execute([$pedido_id, $item['id'], $item['cantidad'], $item['precio']]);
                
                $stmtStock = $pdo->prepare("UPDATE productos SET stock = GREATEST(0, CAST(stock AS SIGNED) - ?) WHERE id = ?");
                $stmtStock->execute([$item['cantidad'], $item['id']]);
            }
            
            $pdo->commit();
            $_SESSION['carrito'] = []; // Vaciar carrito
            echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
            exit;
        }
    } else {
        // Simulación en modo de respaldo si no hay DB
        $_SESSION['carrito'] = [];
        echo json_encode(['success' => true, 'pedido_id' => rand(1500, 9999)]);
        exit;
    }
}

$carrito = $_SESSION['carrito'] ?? [];
$cartCount = array_sum(array_column($carrito, 'cantidad'));

// Calcular totales actuales
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 2000 || $subtotal == 0 ? 0 : 199;
$total = $subtotal + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito de Compras · NexPlay</title>
<meta name="description" content="Gestiona los productos de tu carrito y completa tu pedido en la tienda oficial NexPlay.">
<link rel="stylesheet" href="../css/style.css">
<style>
  .cart-container {
    margin-top: 32px;
  }
  .cart-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 32px;
  }
  .cart-panel {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
  }
  .cart-item {
    display: grid;
    grid-template-columns: 80px 2fr 1fr 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid var(--line);
  }
  .cart-item:last-child {
    border-bottom: none;
  }
  .cart-item img {
    width: 80px;
    height: 60px;
    object-fit: contain;
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-sm);
  }
  .cart-item .name h4 {
    margin: 0;
    font-size: 1.05rem;
    color: #fff;
  }
  .cart-item .name p {
    margin: 4px 0 0;
    font-size: 0.85rem;
    color: var(--muted);
  }
  .cart-item .price {
    font-weight: 600;
    color: #fff;
  }
  .qty-input-wrap {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--line);
    border-radius: 6px;
    overflow: hidden;
    background: var(--bg-alt);
  }
  .qty-input-wrap button {
    background: none;
    border: none;
    color: #fff;
    width: 28px;
    height: 28px;
    font-size: 1rem;
    cursor: pointer;
  }
  .qty-input-wrap input {
    background: none;
    border: none;
    color: #fff;
    width: 32px;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
  }
  .delete-btn {
    background: none;
    border: none;
    color: var(--pink);
    cursor: pointer;
    font-size: 1.2rem;
    padding: 8px;
  }
  .delete-btn:hover {
    color: #ef4444;
  }
  
  .summary-panel {
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 24px;
    height: fit-content;
  }
  .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 16px;
    font-size: 0.95rem;
    color: var(--text);
  }
  .summary-row.total {
    border-top: 1px solid var(--line);
    padding-top: 16px;
    font-size: 1.25rem;
    color: #fff;
    font-weight: 700;
    font-family: var(--font-display);
  }
  .empty-cart-state {
    text-align: center;
    padding: 64px 24px;
  }
  .empty-cart-state svg {
    margin-bottom: 18px;
    color: var(--muted-2);
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
    line-height: 1;
  }

  /* CHECKOUT FORM VIEW */
  .checkout-section {
    display: none;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 32px;
    margin-top: 24px;
  }
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
  }
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .form-group label {
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 500;
  }
  .form-group input, .form-group select {
    background: var(--bg-alt);
    border: 1px solid var(--line);
    padding: 12px;
    border-radius: 8px;
    color: #fff;
    font-size: 0.95rem;
  }
  .form-group input:focus {
    border-color: var(--cyan);
    outline: none;
  }

  /* SIMULATION LOADER VIEW */
  .loader-section {
    display: none;
    text-align: center;
    padding: 64px 32px;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    margin-top: 24px;
  }
  .spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(34,211,238,0.1);
    border-top: 4px solid var(--cyan);
    border-radius: 50%;
    margin: 0 auto 24px;
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
  .loader-steps {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-top: 24px;
  }
  .step-status {
    font-size: 0.95rem;
    color: var(--muted);
    transition: color 0.3s;
  }
  .step-status.active {
    color: var(--cyan);
    font-weight: 600;
  }
  .step-status.completed {
    color: #10b981;
  }

  /* RECEIPT VIEW */
  .receipt-section {
    display: none;
    background: var(--panel);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 40px;
    margin-top: 24px;
    max-width: 640px;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 0 15px 35px rgba(0,0,0,0.5);
  }
  .receipt-header {
    text-align: center;
    border-bottom: 2px dashed var(--line);
    padding-bottom: 24px;
    margin-bottom: 24px;
  }
  .receipt-logo {
    font-size: 1.8rem;
    font-weight: 800;
    color: #fff;
    text-decoration: none;
    font-family: var(--font-display);
  }
  .receipt-logo span {
    color: var(--cyan);
  }
  .receipt-check {
    width: 64px;
    height: 64px;
    background: rgba(16,185,129,0.1);
    border: 2px solid #10b981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #10b981;
    font-size: 2rem;
    margin: 16px auto;
  }
  .receipt-details {
    margin-bottom: 24px;
  }
  .receipt-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 0.92rem;
  }
  .receipt-row .label {
    color: var(--muted);
  }
  .receipt-row .val {
    color: #fff;
    font-weight: 500;
    text-align: right;
  }

  @media (max-width: 768px) {
    .cart-grid {
      grid-template-columns: 1fr;
    }
    .form-grid {
      grid-template-columns: 1fr;
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
          <span class="nav-cart-badge" id="nav-cart-count"><?= $cartCount ?></span>
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

<main class="container cart-container">
  
  <!-- BOTÓN REGRESAR -->
  <div id="btn-back-wrap" style="margin-bottom: 24px;">
    <a href="tienda.php" class="btn btn-ghost" style="padding: 8px 16px; border-radius: 8px; font-size: 0.85rem;">← Seguir comprando</a>
  </div>

  <!-- PASO 1: REVISIÓN DE CARRITO -->
  <div id="step-cart-view">
    <section class="section-head" style="margin-bottom: 24px;">
      <p class="eyebrow">Resumen del pedido</p>
      <h2>Tu Carrito de Compras</h2>
    </section>

    <?php if (empty($carrito)): ?>
      <div class="cart-panel">
        <div class="empty-cart-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9.2a2 2 0 0 0 2-1.6L22 7H6"/></svg>
          <h3 style="color:#fff; margin-bottom:8px;">Tu carrito está vacío</h3>
          <p style="color:var(--muted); margin-bottom:24px;">Agrega productos de la tienda para poder iniciar tu compra.</p>
          <a href="tienda.php" class="btn btn-primary">Explorar Tienda</a>
        </div>
      </div>
    <?php else: ?>
      <div class="cart-grid">
        <div class="cart-panel">
          <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--line); padding-bottom:12px; margin-bottom:8px;">
            <span style="font-weight:600; color:#fff;">Producto</span>
            <span style="font-weight:600; color:#fff; display:inline-block; margin-right:64px;">Subtotal</span>
          </div>
          
          <?php foreach ($carrito as $item): ?>
            <div class="cart-item">
              <img src="../<?= htmlspecialchars($item['imagen']) ?>" alt="<?= htmlspecialchars($item['nombre']) ?>">
              <div class="name">
                <h4><?= htmlspecialchars($item['nombre']) ?></h4>
                <p><?= formatoPrecio($item['precio']) ?> c/u</p>
              </div>
              <div class="qty-col">
                <form action="cart_action.php" method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="producto_id" value="<?= $item['id'] ?>">
                  <div class="qty-input-wrap">
                    <button type="submit" name="cantidad" value="<?= $item['cantidad'] - 1 ?>">-</button>
                    <input type="text" value="<?= $item['cantidad'] ?>" readonly>
                    <button type="submit" name="cantidad" value="<?= $item['cantidad'] + 1 ?>">+</button>
                  </div>
                </form>
              </div>
              <div class="price">
                <?= formatoPrecio($item['precio'] * $item['cantidad']) ?>
              </div>
              <div class="del-col">
                <a href="cart_action.php?action=remove&producto_id=<?= $item['id'] ?>" class="delete-btn">✕</a>
              </div>
            </div>
          <?php endforeach; ?>
          
          <div style="margin-top:24px;">
            <a href="cart_action.php?action=clear" class="btn btn-ghost" style="border-color:var(--pink); color:var(--pink); font-size:0.85rem;">Vaciar Carrito</a>
          </div>
        </div>

        <div class="summary-panel">
          <h3 style="color:#fff; margin-bottom:24px; font-family:var(--font-display);">Resumen de Compra</h3>
          <div class="summary-row">
            <span>Subtotal</span>
            <span><?= formatoPrecio($subtotal) ?></span>
          </div>
          <div class="summary-row">
            <span>Envío</span>
            <span><?= $envio == 0 ? 'Gratis' : formatoPrecio($envio) ?></span>
          </div>
          <?php if ($envio > 0): ?>
            <p style="font-size:0.75rem; color:var(--muted); margin-bottom:16px;">¡Agrega $<?= 2000 - $subtotal ?> más para envío gratis!</p>
          <?php endif; ?>
          <div class="summary-row total">
            <span>Total</span>
            <span><?= formatoPrecio($total) ?></span>
          </div>

          <?php if (isLoggedIn()): ?>
            <button onclick="goToCheckout()" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:24px;">Proceder al Pago</button>
          <?php else: ?>
            <div style="margin-top:24px; background:rgba(251,146,60,0.06); border:1px solid rgba(251,146,60,0.2); padding:16px; border-radius:8px; text-align:center;">
              <p style="font-size:0.85rem; color:var(--orange); margin-bottom:12px;">Inicia sesión para completar tu compra de forma segura.</p>
              <a href="login.php" class="btn btn-primary" style="width:100%; justify-content:center;">Acceder</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- PASO 2: DATOS DE ENVÍO Y PAGO (FORMULARIO) -->
  <div id="step-checkout-view" class="checkout-section">
    <div class="section-head" style="margin-bottom: 24px;">
      <p class="eyebrow">Pasarela Segura SSL</p>
      <h2>Datos de Envío y Pago</h2>
      <p>NexPlay encripta todos sus pagos con protección de grado militar.</p>
    </div>

    <form id="checkout-form" onsubmit="processPayment(event)">
      <h3 style="color:#fff; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:8px;">1. Dirección de Entrega</h3>
      <div class="form-grid">
        <div class="form-group">
          <label for="c_nombre">Nombre Completo de quien recibe *</label>
          <input type="text" id="c_nombre" required value="<?= isLoggedIn() ? htmlspecialchars($u['nombre']) : '' ?>">
        </div>
        <div class="form-group">
          <label for="c_tel">Teléfono de contacto *</label>
          <input type="tel" id="c_tel" required placeholder="55 1234 5678">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group" style="grid-column: span 2;">
          <label for="c_calle">Calle, número exterior e interior *</label>
          <input type="text" id="c_calle" required placeholder="Ej. Av. Reforma 123, Int 4B">
        </div>
      </div>
      <div class="three-col" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:32px;">
        <div class="form-group">
          <label for="c_colonia">Colonia *</label>
          <input type="text" id="c_colonia" required placeholder="Col. Centro">
        </div>
        <div class="form-group">
          <label for="c_ciudad">Delegación / Municipio *</label>
          <input type="text" id="c_ciudad" required placeholder="Juárez">
        </div>
        <div class="form-group">
          <label for="c_cp">Código Postal *</label>
          <input type="text" id="c_cp" required placeholder="06000" pattern="[0-9]{5}">
        </div>
      </div>

      <h3 style="color:#fff; margin-bottom:16px; border-bottom:1px solid var(--line); padding-bottom:8px;">2. Detalles del Pago Bancario</h3>
      <div class="form-grid">
        <div class="form-group">
          <label for="p_tarjeta">Número de Tarjeta (Simulación) *</label>
          <input type="text" id="p_tarjeta" required placeholder="4000 1234 5678 9010" maxlength="19" pattern="\d{4} \d{4} \d{4} \d{4}">
        </div>
        <div class="form-group">
          <label for="p_titular">Nombre del Titular *</label>
          <input type="text" id="p_titular" required placeholder="JUAN PEREZ GONZALEZ">
        </div>
      </div>
      <div class="two-col" style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
        <div class="form-group">
          <label for="p_exp">Fecha de Vencimiento *</label>
          <input type="text" id="p_exp" required placeholder="MM/AA" maxlength="5" pattern="(0[1-9]|1[0-2])\/\d{2}">
        </div>
        <div class="form-group">
          <label for="p_cvv">CVV (Código de seguridad) *</label>
          <input type="password" id="p_cvv" required placeholder="•••" maxlength="3" pattern="\d{3}">
        </div>
      </div>

      <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--line); padding-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="backToCart()">Atrás</button>
        <button type="submit" class="btn btn-primary" style="padding:14px 28px;">Pagar <?= formatoPrecio($total) ?> Seguramente</button>
      </div>
    </form>
  </div>

  <!-- PASO 3: CARGANDO (SIMULACIÓN DE PASARELA) -->
  <div id="step-loading-view" class="loader-section">
    <div class="spinner"></div>
    <h3 style="color:#fff; margin-bottom:8px;">Procesando Pago Seguro</h3>
    <p style="color:var(--muted); max-width:400px; margin: 0 auto 24px;">Por favor, no recargues la página ni cierres la pestaña.</p>
    
    <div class="loader-steps">
      <div id="load-step-1" class="step-status">🔍 Conectando con servidor bancario emisor...</div>
      <div id="load-step-2" class="step-status">🔒 Encriptando credenciales con clave RSA-4096...</div>
      <div id="load-step-3" class="step-status">💳 Verificando fondos y autorizando cargo...</div>
      <div id="load-step-4" class="step-status">📦 Generando orden de entrega en NexPlay...</div>
    </div>
  </div>

  <!-- PASO 4: RECIBO DE COMPRA (TICKET EXITOSO) -->
  <div id="step-receipt-view" class="receipt-section">
    <div class="receipt-header">
      <a href="../index.php" class="receipt-logo"><span class="dot"></span>Nex<span>Play</span></a>
      <div class="receipt-check">✓</div>
      <h3 style="color:#fff; margin-bottom:4px; font-family:var(--font-display);">¡Pago Autorizado Exitosamente!</h3>
      <p style="color:var(--muted); font-size:0.85rem; margin:0;">Gracias por tu compra. Tu pedido está en camino.</p>
    </div>

    <div class="receipt-details">
      <div class="receipt-row">
        <span class="label">ID de Pedido:</span>
        <span class="val" id="rec-pedido-id" style="font-family:var(--font-mono); color:var(--cyan); font-weight:700;">#0</span>
      </div>
      <div class="receipt-row">
        <span class="label">Fecha y Hora:</span>
        <span class="val" id="rec-fecha"><?= date('d/m/Y H:i') ?></span>
      </div>
      <div class="receipt-row">
        <span class="label">Estado de Entrega:</span>
        <span class="val" style="color:#10b981; font-weight:bold;">Preparando envío</span>
      </div>
      <div class="receipt-row">
        <span class="label">Entregar a:</span>
        <span class="val" id="rec-nombre">Nombre</span>
      </div>
      <div class="receipt-row" style="align-items: flex-start;">
        <span class="label">Dirección:</span>
        <span class="val" id="rec-direccion" style="max-width:60%; font-size:0.85rem;">Dirección completa</span>
      </div>
      <div class="receipt-row">
        <span class="label">Método de Pago:</span>
        <span class="val" id="rec-tarjeta">Visa terminada en 4242</span>
      </div>
      
      <div style="border-top:1px dashed var(--line); margin: 16px 0; padding-top: 16px;"></div>
      
      <div class="receipt-row">
        <span class="label">Subtotal:</span>
        <span class="val" id="rec-subtotal">$0</span>
      </div>
      <div class="receipt-row">
        <span class="label">Costo de Envío:</span>
        <span class="val" id="rec-envio">$0</span>
      </div>
      <div class="receipt-row" style="font-size:1.1rem; font-weight:bold; margin-top:8px;">
        <span class="label" style="color:#fff;">Monto Total Cobrado:</span>
        <span class="val" id="rec-total" style="color:var(--cyan); font-family:var(--font-display); font-size:1.2rem;">$0</span>
      </div>
    </div>

    <div style="text-align:center; border-top:1px solid var(--line); padding-top:24px;">
      <p style="font-size:0.85rem; color:var(--muted); margin-bottom:16px;">Se ha enviado un correo electrónico de confirmación con tu guía de rastreo.</p>
      <a href="tienda.php" class="btn btn-primary" style="display:inline-flex; width:100%; justify-content:center;">Regresar al Catálogo</a>
    </div>
  </div>

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
// Navegación del Checkout
function goToCheckout() {
  document.getElementById('step-cart-view').style.display = 'none';
  document.getElementById('btn-back-wrap').style.display = 'none';
  document.getElementById('step-checkout-view').style.display = 'block';
}

function backToCart() {
  document.getElementById('step-checkout-view').style.display = 'none';
  document.getElementById('btn-back-wrap').style.display = 'block';
  document.getElementById('step-cart-view').style.display = 'block';
}

// Formatear tarjeta al escribir (agrega espacios automáticamente)
const tarjetaInput = document.getElementById('p_tarjeta');
tarjetaInput.addEventListener('input', function(e) {
  let val = e.target.value.replace(/\D/g, '');
  let formatted = '';
  for(let i=0; i<val.length && i<16; i++) {
    if(i > 0 && i % 4 === 0) formatted += ' ';
    formatted += val[i];
  }
  e.target.value = formatted;
});

// Formatear fecha expiración (MM/AA)
const expInput = document.getElementById('p_exp');
expInput.addEventListener('input', function(e) {
  let val = e.target.value.replace(/\D/g, '');
  let formatted = '';
  if (val.length > 0) {
    formatted = val.substring(0,2);
    if (val.length > 2) {
      formatted += '/' + val.substring(2,4);
    }
  }
  e.target.value = formatted;
});

// Procesar simulación de pago con animación y guardado AJAX
function processPayment(event) {
  event.preventDefault();
  
  // Habilitar vista de carga
  document.getElementById('step-checkout-view').style.display = 'none';
  document.getElementById('step-loading-view').style.display = 'block';

  const steps = [
    document.getElementById('load-step-1'),
    document.getElementById('load-step-2'),
    document.getElementById('load-step-3'),
    document.getElementById('load-step-4')
  ];

  // Ejecutar pasos de carga secuenciales
  setTimeout(() => {
    steps[0].classList.add('active');
    steps[0].innerHTML = '✓ Servidor bancario emisor verificado.';
    steps[0].classList.add('completed');
  }, 800);

  setTimeout(() => {
    steps[1].classList.add('active');
    steps[1].innerHTML = '✓ Datos encriptados mediante canal de seguridad SSL.';
    steps[1].classList.add('completed');
  }, 1600);

  setTimeout(() => {
    steps[2].classList.add('active');
    steps[2].innerHTML = '✓ Transacción de cargo bancario autorizada y aprobada.';
    steps[2].classList.add('completed');
  }, 2400);

  setTimeout(() => {
    steps[3].classList.add('active');
    steps[3].innerHTML = '⏳ Creando tu orden de compra segura en NexPlay...';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'carrito.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.success) {
            steps[3].innerHTML = '✓ Pedido registrado con éxito en el sistema NexPlay.';
            steps[3].classList.add('completed');
            
            // Llenar datos en recibo
            setTimeout(() => {
              document.getElementById('rec-pedido-id').innerText = '#' + res.pedido_id;
              document.getElementById('rec-nombre').innerText = document.getElementById('c_nombre').value;
              
              const direccion = document.getElementById('c_calle').value + ', Col. ' + 
                                document.getElementById('c_colonia').value + ', ' + 
                                document.getElementById('c_ciudad').value + ', CP ' + 
                                document.getElementById('c_cp').value;
              document.getElementById('rec-direccion').innerText = direccion;
              
              const numTarjeta = document.getElementById('p_tarjeta').value;
              const terminacion = numTarjeta.substring(numTarjeta.length - 4);
              document.getElementById('rec-tarjeta').innerText = 'Tarjeta terminada en ' + terminacion;
              
              document.getElementById('rec-subtotal').innerText = '<?= formatoPrecio($subtotal) ?>';
              document.getElementById('rec-envio').innerText = '<?= $envio == 0 ? 'Gratis' : formatoPrecio($envio) ?>';
              document.getElementById('rec-total').innerText = '<?= formatoPrecio($total) ?>';
              
              // Actualizar badge del carrito de la cabecera a vacío
              const badge = document.getElementById('nav-cart-count');
              if (badge) badge.style.display = 'none';

              // Mostrar recibo
              document.getElementById('step-loading-view').style.display = 'none';
              document.getElementById('step-receipt-view').style.display = 'block';
            }, 800);
          } else {
            alert('Error al registrar pedido: ' + res.message);
            location.reload();
          }
        } catch(e) {
          alert('Error de procesamiento de datos.');
          location.reload();
        }
      }
    };
    
    xhr.send('ajax_checkout=1');
  }, 3200);
}
</script>

<script src="../js/script.js"></script>
</body>
</html>
