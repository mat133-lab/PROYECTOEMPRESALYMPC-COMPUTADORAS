<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Usuario no autorizado']);
    return;
}

// Recibir datos del carrito desde JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['carrito']) || empty($data['carrito'])) {
    echo json_encode(['ok' => false, 'msg' => 'El carrito está vacío']);
    return;
}

$carrito = $data['carrito'];
$id_usuario = $_SESSION['id_usuario'];

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Calcular total del pedido
    $total = 0;
    $productos = [];

    // Obtener información de los productos y validar stock
    foreach ($carrito as $item) {
        $id_producto = intval($item['id']);
        $cantidad = intval($item['cantidad']);

        $stmt = $conn->prepare("SELECT id_producto, nombre, precio, unidades FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("Producto no encontrado: ID " . $id_producto);
        }

        if ($producto['unidades'] < $cantidad) {
            throw new Exception("Stock insuficiente para: " . $producto['nombre']);
        }

        $subtotal = floatval($producto['precio']) * $cantidad;
        $total += $subtotal;

        $productos[] = [
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_unitario' => $producto['precio'],
            'unidades_actuales' => $producto['unidades']
        ];
    }

    // 1. Insertar el pedido general en la tabla pedidos
    $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, fecha_pedido, total) VALUES (?, NOW(), ?)");
    $stmt->execute([$id_usuario, $total]);
    $id_pedido = $conn->lastInsertId();

    // 2. Registrar cada ítem en detalles_pedido y actualizar stock
    foreach ($productos as $prod) {
        // Insertar en detalles_pedido
        $stmt = $conn->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_pedido, $prod['id_producto'], $prod['cantidad'], $prod['precio_unitario']]);

        // Actualizar stock (restar unidades compradas)
        $nuevo_stock = $prod['unidades_actuales'] - $prod['cantidad'];
        $stmt = $conn->prepare("UPDATE productos SET unidades = ? WHERE id_producto = ?");
        $stmt->execute([$nuevo_stock, $prod['id_producto']]);
    }

    // Confirmar transacción
    $conn->commit();

    // Limpiar el carrito de la sesión
    $_SESSION['cart'] = [];

    echo json_encode([
        'ok' => true, 
        'msg' => 'Compra procesada correctamente',
        'id_pedido' => $id_pedido,
        'total' => $total
    ]);

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['ok' => false, 'msg' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>



