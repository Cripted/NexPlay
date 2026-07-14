<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/data.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $id = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 1);
    if ($cantidad <= 0) $cantidad = 1;
    
    $producto = obtenerProductoPorId($id);
    if ($producto) {
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => (float)$producto['precio'],
                'imagen' => $producto['imagen'],
                'cantidad' => $cantidad
            ];
        }
    }
    
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total_items' => array_sum(array_column($_SESSION['carrito'], 'cantidad')),
            'cart' => $_SESSION['carrito']
        ]);
        exit;
    }
    header('Location: carrito.php');
    exit;
}

if ($action === 'remove') {
    $id = (int)($_POST['producto_id'] ?? $_GET['producto_id'] ?? 0);
    if (isset($_SESSION['carrito'][$id])) {
        unset($_SESSION['carrito'][$id]);
    }
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total_items' => array_sum(array_column($_SESSION['carrito'], 'cantidad')),
            'cart' => $_SESSION['carrito']
        ]);
        exit;
    }
    header('Location: carrito.php');
    exit;
}

if ($action === 'update') {
    $id = (int)($_POST['producto_id'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 1);
    if ($cantidad <= 0) {
        unset($_SESSION['carrito'][$id]);
    } else {
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
        }
    }
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total_items' => array_sum(array_column($_SESSION['carrito'], 'cantidad')),
            'cart' => $_SESSION['carrito']
        ]);
        exit;
    }
    header('Location: carrito.php');
    exit;
}

if ($action === 'clear') {
    $_SESSION['carrito'] = [];
    if (isset($_POST['ajax']) || isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'total_items' => 0,
            'cart' => []
        ]);
        exit;
    }
    header('Location: carrito.php');
    exit;
}

header('Location: tienda.php');
exit;
