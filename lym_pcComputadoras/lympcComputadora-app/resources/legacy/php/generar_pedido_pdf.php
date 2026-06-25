<?php
session_start();
require_once '../includes/db.php';
require_once '../vendor/autoload.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    echo 'Pedido inválido'; return;
}

$id_pedido = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT p.id_pedido, p.fecha_pedido, p.total, u.id_usuario, u.usuario, u.correo, u.cedula FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario WHERE p.id_pedido = ?");
$stmt->execute([$id_pedido]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    http_response_code(404);
    echo 'Pedido no encontrado'; return;
}

$stmtItems = $conn->prepare("SELECT dp.cantidad, dp.precio_unitario, pr.nombre, pr.serie FROM detalles_pedido dp JOIN productos pr ON pr.id_producto = dp.id_producto WHERE dp.id_pedido = ? ORDER BY dp.id_detalle ASC");
$stmtItems->execute([$id_pedido]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float)$item['cantidad'] * (float)$item['precio_unitario'];
}

$fecha = date('d/m/Y H:i', strtotime($pedido['fecha_pedido']));
$totalFormatted = number_format((float)$pedido['total'], 2);
$cliente = htmlspecialchars($pedido['usuario']);
$correo = htmlspecialchars($pedido['correo']);
$cedula = htmlspecialchars($pedido['cedula'] ?? 'N/A');
$itemsHtml = '';
foreach ($items as $item) {
    $lineTotal = (float)$item['cantidad'] * (float)$item['precio_unitario'];
    $itemsHtml .= "
        <tr>
            <td style='padding:10px 8px; border-bottom:1px solid #e5e7eb;'>" . htmlspecialchars($item['nombre']) . "</td>
            <td style='padding:10px 8px; border-bottom:1px solid #e5e7eb; text-align:center;'>" . (int)$item['cantidad'] . "</td>
            <td style='padding:10px 8px; border-bottom:1px solid #e5e7eb; text-align:right;'>$" . number_format((float)$item['precio_unitario'], 2) . "</td>
            <td style='padding:10px 8px; border-bottom:1px solid #e5e7eb; text-align:right;'>$" . number_format($lineTotal, 2) . "</td>
        </tr>";
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Comprobante de Pedido #{$pedido['id_pedido']}</title>
  <link rel="stylesheet" href="/css/pedido_pdf.css" />
</head>
<body class="pdf-page">
  <img src="/img/headerlym.png" class="header-img" alt="Encabezado" />
  <div class="card">
    <div class="top">
      <div>
        <div class="badge">Comprobante de compra</div>
        <h1>L&M PC Computadoras</h1>
        <div class="muted">Documento emitido para el seguimiento de su pedido</div>
      </div>
      <div style="text-align:right;">
        <div class="muted">Pedido #{$pedido['id_pedido']}</div>
        <div style="font-size:18px; font-weight:bold; color:#111827;">{$totalFormatted}</div>
        <div class="muted">Fecha: {$fecha}</div>
      </div>
    </div>

    <div class="grid">
      <div class="panel">
        <div class="label">Cliente</div>
        <div style="font-size:15px; font-weight:bold;">{$cliente}</div>
        <div class="muted">Correo: {$correo}</div>
        <div class="muted">Cédula: {$cedula}</div>
      </div>
      <div class="panel">
        <div class="label">Estado del pedido</div>
        <div style="font-size:15px; font-weight:bold;">Procesado correctamente</div>
        <div class="muted">Método: Compra en línea</div>
        <div class="muted">Referencia: PED-{$pedido['id_pedido']}</div>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th style="text-align:center;">Cant.</th>
          <th style="text-align:right;">Precio unit.</th>
          <th style="text-align:right;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        {$itemsHtml}
      </tbody>
    </table>

    <div class="totals">
      <table>
        <tr><td style="text-align:right; font-weight:bold;">Total</td><td style="text-align:right; font-weight:bold;">{$totalFormatted}</td></tr>
      </table>
    </div>

    <div class="footer">Gracias por confiar en L&M PC Computadoras. Este comprobante es generado automáticamente para su respaldo y seguimiento.</div>
  </div>
  <img src="/img/footerlym.png" class="footer-img" alt="Pie de página" />
</body>
</html>
HTML;

$dompdf = new Dompdf\Dompdf(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (isset($_GET['download']) && $_GET['download'] == '1') {
    $dompdf->stream('pedido_' . $id_pedido . '.pdf', ['Attachment' => true]);
} else {
    $dompdf->stream('pedido_' . $id_pedido . '.pdf', ['Attachment' => false]);
}


