<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

function cart_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    cart_response(['ok' => false, 'msg' => 'Inicia sesión para usar el carrito.'], 401);
}

$action = $_POST['action'] ?? 'add';

try {
    if ($action === 'list') {
        $cart = $_SESSION['cart'] ?? [];
        $items = [];
        foreach ($cart as $productId => $quantity) {
            $stmt = $conn->prepare(
                'SELECT id_producto, nombre, serie, fecha, precio, imagen, categoria, unidades
                 FROM productos WHERE id_producto = ?'
            );
            $stmt->execute([(int) $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            $items[] = [
                'producto' => [
                    'id' => (int) $product['id_producto'],
                    'nombre' => (string) $product['nombre'],
                    'serie' => (string) $product['serie'],
                    'fecha' => (string) $product['fecha'],
                    'precio' => (string) $product['precio'],
                    'imagen' => (string) $product['imagen'],
                    'categoria' => (string) $product['categoria'],
                ],
                'cantidad' => (int) $quantity,
                'stock' => max(0, (int) $product['unidades'] - (int) $quantity),
            ];
        }

        cart_response(['ok' => true, 'items' => $items]);
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        cart_response(['ok' => true, 'msg' => 'Carrito vaciado.']);
    }

    if ($action !== 'add') {
        cart_response(['ok' => false, 'msg' => 'Acción no soportada.'], 400);
    }

    $productId = $_POST['product_id'] ?? '';
    $quantity = filter_var($_POST['qty'] ?? 1, FILTER_VALIDATE_INT);
    if (!is_string($productId) || !ctype_digit($productId) || (int) $productId < 1
        || $quantity === false || $quantity < 1 || $quantity > 99) {
        cart_response(['ok' => false, 'msg' => 'Producto o cantidad no válidos.'], 400);
    }

    $stmt = $conn->prepare(
        'SELECT id_producto, nombre, serie, fecha, precio, imagen, categoria, unidades
         FROM productos WHERE id_producto = ?'
    );
    $stmt->execute([(int) $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        cart_response(['ok' => false, 'msg' => 'El producto ya no está disponible.'], 404);
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $cartQuantity = (int) ($_SESSION['cart'][$productId] ?? 0);
    if ($cartQuantity + $quantity > (int) $product['unidades']) {
        cart_response([
            'ok' => false,
            'msg' => 'Stock insuficiente.',
            'stock' => max(0, (int) $product['unidades'] - $cartQuantity),
        ], 409);
    }

    $_SESSION['cart'][$productId] = $cartQuantity + $quantity;
    cart_response([
        'ok' => true,
        'msg' => 'Producto agregado al carrito.',
        'stock' => max(0, (int) $product['unidades'] - $cartQuantity - $quantity),
        'producto' => [
            'id' => (int) $product['id_producto'],
            'nombre' => (string) $product['nombre'],
            'serie' => (string) $product['serie'],
            'fecha' => (string) $product['fecha'],
            'precio' => (string) $product['precio'],
            'imagen' => (string) $product['imagen'],
            'categoria' => (string) $product['categoria'],
        ],
        'cantidad' => $cartQuantity + $quantity,
    ]);
} catch (Throwable $exception) {
    error_log('Error al actualizar el carrito: ' . $exception->getMessage());
    cart_response(['ok' => false, 'msg' => 'No se pudo actualizar el carrito. Intenta nuevamente.'], 500);
}
