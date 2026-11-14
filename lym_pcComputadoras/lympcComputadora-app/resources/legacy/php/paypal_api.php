<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/paypal_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    paypal_json_response(['ok' => false, 'msg' => 'Método no permitido.'], 405);
}

if (!isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    paypal_json_response(['ok' => false, 'msg' => 'Inicia sesión para pagar.'], 401);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['action'])) {
    paypal_json_response(['ok' => false, 'msg' => 'Solicitud de pago no válida.'], 400);
}

$userId = (int) $_SESSION['id_usuario'];
$requestReference = bin2hex(random_bytes(5));

try {
    paypal_ensure_orders_table($conn);

    if ($data['action'] === 'config') {
        $config = paypal_config();
        if ($config['client_id'] === '' || $config['client_secret'] === '') {
            paypal_json_response(['ok' => false, 'msg' => 'PayPal no está configurado. Define PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET.'], 503);
        }
        paypal_json_response(['ok' => true, 'clientId' => $config['client_id']]);
    }

    if ($data['action'] === 'recover-completed') {
        $pendingOrders = array_slice(
            array_reverse($_SESSION['paypal_orders'] ?? [], true),
            0,
            10,
            true
        );

        foreach ($pendingOrders as $orderId => $snapshot) {
            if (!is_array($snapshot) || (int) ($snapshot['user_id'] ?? 0) !== $userId) {
                continue;
            }

            $paypalOrder = paypal_api_request('GET', '/v2/checkout/orders/' . rawurlencode((string) $orderId));
            $unit = $paypalOrder['purchase_units'][0] ?? null;
            $capture = $unit['payments']['captures'][0] ?? null;
            if (($paypalOrder['status'] ?? '') !== 'COMPLETED'
                || !is_array($unit)
                || ($unit['custom_id'] ?? '') !== ($snapshot['custom_id'] ?? '')
                || !is_array($capture)
                || ($capture['status'] ?? '') !== 'COMPLETED') {
                continue;
            }

            $amount = $capture['amount'] ?? [];
            $paidCents = isset($amount['value']) ? (int) round((float) $amount['value'] * 100) : -1;
            if (($amount['currency_code'] ?? '') === 'USD'
                && $paidCents === (int) ($snapshot['total_cents'] ?? -2)) {
                paypal_json_response(['ok' => true, 'orderID' => (string) $orderId]);
            }
        }

        paypal_json_response(['ok' => true, 'orderID' => null]);
    }

    if ($data['action'] === 'create-order') {
        $cart = $_SESSION['cart'] ?? [];
        if (!is_array($cart) || $cart === []) {
            paypal_json_response(['ok' => false, 'msg' => 'El carrito está vacío.'], 400);
        }

        $items = [];
        $totalCents = 0;
        foreach ($cart as $productId => $quantity) {
            $validQuantityType = is_int($quantity)
                || (is_string($quantity) && ctype_digit($quantity));
            if (!ctype_digit((string) $productId) || (int) $productId < 1 || !$validQuantityType) {
                paypal_json_response(['ok' => false, 'msg' => 'El carrito contiene cantidades no válidas.'], 400);
            }
            $quantity = (int) $quantity;
            if ($quantity < 1 || $quantity > 99) {
                paypal_json_response(['ok' => false, 'msg' => 'El carrito contiene cantidades no válidas.'], 400);
            }

            $stmt = $conn->prepare(
                'SELECT id_producto, nombre, serie, precio, unidades FROM productos WHERE id_producto = ?'
            );
            $stmt->execute([(int) $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                paypal_json_response(['ok' => false, 'msg' => 'Un producto del carrito ya no está disponible.'], 409);
            }
            if ((int) $product['unidades'] < (int) $quantity) {
                paypal_json_response([
                    'ok' => false,
                    'msg' => 'Stock insuficiente para ' . $product['nombre'] . '. Actualiza el carrito.',
                ], 409);
            }

            $unitCents = (int) round((float) $product['precio'] * 100);
            if ($unitCents < 1) {
                paypal_json_response(['ok' => false, 'msg' => 'Un producto tiene un precio no válido.'], 409);
            }

            $totalCents += $unitCents * (int) $quantity;
            $items[] = [
                'id_producto' => (int) $product['id_producto'],
                'nombre' => (string) $product['nombre'],
                'cantidad' => (int) $quantity,
                'precio_unitario' => number_format($unitCents / 100, 2, '.', ''),
            ];
        }

        if ($totalCents < 1) {
            paypal_json_response(['ok' => false, 'msg' => 'El total del carrito no es válido.'], 400);
        }

        $customId = bin2hex(random_bytes(16));
        $paypalItems = array_map(static function (array $item): array {
            return [
                'name' => function_exists('mb_substr')
                    ? mb_substr($item['nombre'], 0, 127, 'UTF-8')
                    : substr($item['nombre'], 0, 127),
                'sku' => (string) $item['id_producto'],
                'quantity' => (string) $item['cantidad'],
                'unit_amount' => [
                    'currency_code' => 'USD',
                    'value' => $item['precio_unitario'],
                ],
            ];
        }, $items);

        $order = paypal_api_request('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'custom_id' => $customId,
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($totalCents / 100, 2, '.', ''),
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => 'USD',
                            'value' => number_format($totalCents / 100, 2, '.', ''),
                        ],
                    ],
                ],
                'items' => $paypalItems,
            ]],
            'application_context' => [
                'brand_name' => 'L&M PC Computadoras',
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (empty($order['id']) || ($order['status'] ?? '') !== 'CREATED') {
            error_log('PayPal no devolvió una orden creada correctamente.');
            paypal_json_response(['ok' => false, 'msg' => 'PayPal no pudo iniciar el pago. Inténtalo nuevamente.'], 502);
        }

        $_SESSION['paypal_orders'][$order['id']] = [
            'custom_id' => $customId,
            'items' => $items,
            'total_cents' => $totalCents,
            'user_id' => $userId,
        ];

        paypal_json_response(['ok' => true, 'orderID' => $order['id']]);
    }

    if ($data['action'] === 'capture-order') {
        $orderId = $data['orderID'] ?? '';
        if (!is_string($orderId) || !preg_match('/^[A-Z0-9]{10,64}$/i', $orderId)) {
            paypal_json_response(['ok' => false, 'msg' => 'La referencia de pago no es válida.'], 400);
        }

        $stmt = $conn->prepare(
            'SELECT id_pedido, id_usuario, total FROM paypal_pedidos WHERE paypal_order_id = ?'
        );
        $stmt->execute([$orderId]);
        $savedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($savedOrder) {
            if ((int) $savedOrder['id_usuario'] !== $userId) {
                paypal_json_response(['ok' => false, 'msg' => 'No tienes permiso para consultar este pago.'], 403);
            }
            paypal_json_response([
                'ok' => true,
                'msg' => 'Pago registrado correctamente.',
                'id_pedido' => (int) $savedOrder['id_pedido'],
                'total' => (float) $savedOrder['total'],
            ]);
        }

        $snapshot = $_SESSION['paypal_orders'][$orderId] ?? null;
        if (!is_array($snapshot) || (int) ($snapshot['user_id'] ?? 0) !== $userId) {
            paypal_json_response(['ok' => false, 'msg' => 'La orden no pertenece a esta sesión. Inicia el pago nuevamente.'], 403);
        }

        $paypalOrder = paypal_api_request('GET', '/v2/checkout/orders/' . rawurlencode($orderId));
        $purchaseUnit = $paypalOrder['purchase_units'][0] ?? null;
        if (!is_array($purchaseUnit) || ($purchaseUnit['custom_id'] ?? '') !== $snapshot['custom_id']) {
            paypal_json_response(['ok' => false, 'msg' => 'No se pudo verificar que la orden corresponda a este carrito.'], 409);
        }

        if (($paypalOrder['status'] ?? '') === 'APPROVED') {
            try {
                $paypalOrder = paypal_api_request(
                    'POST',
                    '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture',
                    new stdClass()
                );
            } catch (RuntimeException $captureError) {
                $paypalOrder = paypal_api_request('GET', '/v2/checkout/orders/' . rawurlencode($orderId));
                if (($paypalOrder['status'] ?? '') !== 'COMPLETED') {
                    throw $captureError;
                }
            }
        }

        if (($paypalOrder['status'] ?? '') !== 'COMPLETED') {
            paypal_json_response(['ok' => false, 'msg' => 'PayPal todavía no confirma el pago.'], 409);
        }

        $capture = $paypalOrder['purchase_units'][0]['payments']['captures'][0] ?? null;
        if (!is_array($capture) || ($capture['status'] ?? '') !== 'COMPLETED') {
            paypal_json_response(['ok' => false, 'msg' => 'PayPal no confirmó el cobro.'], 409);
        }

        $paidAmount = $capture['amount'] ?? [];
        $paidCents = isset($paidAmount['value']) ? (int) round((float) $paidAmount['value'] * 100) : -1;
        if (($paidAmount['currency_code'] ?? '') !== 'USD' || $paidCents !== (int) $snapshot['total_cents']) {
            error_log('PayPal confirmó una orden con importe distinto al carrito.');
            paypal_json_response(['ok' => false, 'msg' => 'El importe confirmado no coincide con el carrito. Contacta al soporte.'], 409);
        }

        $conn->beginTransaction();
        $stmt = $conn->prepare(
            'SELECT id_pedido, id_usuario, total FROM paypal_pedidos WHERE paypal_order_id = ?'
        );
        $stmt->execute([$orderId]);
        $savedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($savedOrder) {
            $conn->commit();
            if ((int) $savedOrder['id_usuario'] !== $userId) {
                paypal_json_response(['ok' => false, 'msg' => 'No tienes permiso para consultar este pago.'], 403);
            }
            paypal_json_response([
                'ok' => true,
                'msg' => 'Pago registrado correctamente.',
                'id_pedido' => (int) $savedOrder['id_pedido'],
                'total' => (float) $savedOrder['total'],
            ]);
        }

        $total = number_format(((int) $snapshot['total_cents']) / 100, 2, '.', '');
        $stmt = $conn->prepare('INSERT INTO pedidos (id_usuario, total) VALUES (?, ?)');
        $stmt->execute([$userId, $total]);
        $pedidoId = (int) $conn->lastInsertId();

        foreach ($snapshot['items'] as $item) {
            $stock = $conn->prepare(
                'UPDATE productos SET unidades = unidades - ? WHERE id_producto = ? AND unidades >= ?'
            );
            $stock->execute([$item['cantidad'], $item['id_producto'], $item['cantidad']]);
            if ($stock->rowCount() !== 1) {
                throw new RuntimeException('El inventario cambió mientras se completaba el pago. Contacta al soporte con la orden PayPal ' . $orderId . '.');
            }

            $detail = $conn->prepare(
                'INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)'
            );
            $detail->execute([
                $pedidoId,
                $item['id_producto'],
                $item['cantidad'],
                $item['precio_unitario'],
            ]);
        }

        $payment = $conn->prepare(
            'INSERT INTO paypal_pedidos (paypal_order_id, id_pedido, id_usuario, total) VALUES (?, ?, ?, ?)'
        );
        $payment->execute([$orderId, $pedidoId, $userId, $total]);
        $conn->commit();

        $_SESSION['cart'] = [];
        unset($_SESSION['paypal_orders'][$orderId]);

        paypal_json_response([
            'ok' => true,
            'msg' => 'Pago confirmado y pedido registrado.',
            'id_pedido' => $pedidoId,
            'total' => (float) $total,
        ]);
    }

    paypal_json_response(['ok' => false, 'msg' => 'Acción de pago no soportada.'], 400);
} catch (RuntimeException $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error en checkout PayPal [' . $requestReference . ']: ' . $exception->getMessage());
    paypal_json_response([
        'ok' => false,
        'msg' => $exception->getMessage(),
        'reference' => $requestReference,
        'orderID' => $data['orderID'] ?? null,
    ], 502);
} catch (Throwable $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('Error al registrar un pago PayPal [' . $requestReference . ']: ' . $exception->getMessage());
    paypal_json_response([
        'ok' => false,
        'msg' => 'No se pudo completar la verificación o registrar el pedido. Conserva el ID de orden de PayPal y reintenta confirmar; no inicies otro pago.',
        'reference' => $requestReference,
        'orderID' => $data['orderID'] ?? null,
    ], 500);
}
