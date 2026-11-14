<?php
header('Content-Type: application/json; charset=utf-8');
http_response_code(410);
echo json_encode([
    'ok' => false,
    'msg' => 'El registro de pedidos solo se permite después de confirmar el pago PayPal.',
], JSON_UNESCAPED_UNICODE);
