<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/pedido_pdf.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit('Debe iniciar sesión para consultar este comprobante.');
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    exit('Pedido inválido');
}

$idPedido = (int) $_GET['id'];
if (strtolower($_SESSION['rol'] ?? '') !== 'admin') {
    $stmt = $conn->prepare('SELECT id_usuario FROM pedidos WHERE id_pedido = ?');
    $stmt->execute([$idPedido]);
    $idUsuarioPedido = $stmt->fetchColumn();
    if ($idUsuarioPedido === false || (int) $idUsuarioPedido !== (int) ($_SESSION['id_usuario'] ?? 0)) {
        http_response_code($idUsuarioPedido === false ? 404 : 403);
        exit('No tienes permiso para consultar este comprobante.');
    }
}

try {
    $html = buildPedidoPdfHtml($conn, $idPedido);
} catch (RuntimeException $exception) {
    if ($exception->getMessage() === 'Pedido no encontrado') {
        http_response_code(404);
        exit('Pedido no encontrado');
    }

    error_log('Error al generar comprobante PDF: ' . $exception->getMessage());
    http_response_code(500);
    exit('No se pudo generar el comprobante PDF.');
}

$dompdf = new Dompdf\Dompdf(['isHtml5ParserEnabled' => true]);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
renderPedidoPdf($dompdf);
$dompdf->stream('pedido_' . $idPedido . '.pdf', [
    'Attachment' => isset($_GET['download']) && $_GET['download'] === '1',
]);
