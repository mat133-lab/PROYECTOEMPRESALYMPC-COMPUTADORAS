<?php

require_once __DIR__ . '/../vendor/autoload.php';

function pedidoPdfEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pedidoPdfImagePath(string $filename): string
{
    $candidates = [
        __DIR__ . '/../img/' . $filename,
        dirname(__DIR__, 2) . '/public/img/' . $filename,
    ];
    $imagePath = false;
    foreach ($candidates as $candidate) {
        $resolved = realpath($candidate);
        if ($resolved !== false && is_file($resolved)) {
            $imagePath = $resolved;
            break;
        }
    }

    if ($imagePath === false) {
        throw new RuntimeException('No se encontró la imagen PDF: ' . $filename);
    }

    if (getimagesize($imagePath) === false) {
        throw new RuntimeException('No se pudo leer la imagen PDF: ' . $filename);
    }

    return $imagePath;
}

function renderPedidoPdf(Dompdf\Dompdf $dompdf): void
{
    $headerPath = pedidoPdfImagePath('headerlym.png');
    $footerPath = pedidoPdfImagePath('footerlym.png');
    $headerSize = getimagesize($headerPath);
    $footerSize = getimagesize($footerPath);
    if ($headerSize === false || $footerSize === false) {
        throw new RuntimeException('No se pudieron leer las dimensiones de las imágenes del comprobante.');
    }

    $dompdf->render();
    $canvas = $dompdf->getCanvas();
    $canvas->page_script(static function ($pageNumber, $pageCount, $canvas) use ($headerPath, $footerPath, $headerSize, $footerSize): void {
        $pageWidth = $canvas->get_width();
        $pageHeight = $canvas->get_height();
        $headerHeight = $pageWidth * $headerSize[1] / $headerSize[0];
        $footerHeight = $pageWidth * $footerSize[1] / $footerSize[0];

        $canvas->image($headerPath, 0, 0, $pageWidth, $headerHeight);
        $canvas->image($footerPath, 0, $pageHeight - $footerHeight, $pageWidth, $footerHeight);
    });
}

function pedidoPdfStyles(): string
{
    $candidates = [
        __DIR__ . '/../css/pedido_pdf.css',
        __DIR__ . '/../../../public/css/pedido_pdf.css',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            $styles = file_get_contents($path);
            if ($styles !== false) {
                return $styles;
            }
        }
    }

    throw new RuntimeException('No se pudo leer la hoja de estilos del comprobante PDF.');
}

function buildPedidoPdfHtml(PDO $conn, int $id_pedido): string
{
    $stmt = $conn->prepare("SELECT p.id_pedido, p.fecha_pedido, p.total, u.id_usuario, u.usuario, u.correo, u.cedula FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario WHERE p.id_pedido = ?");
    $stmt->execute([$id_pedido]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new RuntimeException('Pedido no encontrado');
    }

    $stmtItems = $conn->prepare("SELECT dp.cantidad, dp.precio_unitario, pr.nombre FROM detalles_pedido dp JOIN productos pr ON pr.id_producto = dp.id_producto WHERE dp.id_pedido = ? ORDER BY dp.id_detalle ASC");
    $stmtItems->execute([$id_pedido]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $itemsHtml = '';
    foreach ($items as $item) {
        $lineTotal = (float) $item['cantidad'] * (float) $item['precio_unitario'];
        $itemsHtml .= '
            <p class="product">Producto: ' . pedidoPdfEscape($item['nombre'])
            . ' | Cantidad: ' . (int) $item['cantidad']
            . ' | Precio unitario: $' . number_format((float) $item['precio_unitario'], 2)
            . ' | Subtotal: $' . number_format($lineTotal, 2) . '</p>';
    }

    $idPedido = (int) $pedido['id_pedido'];
    $fecha = pedidoPdfEscape(date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])));
    $total = number_format((float) $pedido['total'], 2);
    $cliente = pedidoPdfEscape($pedido['usuario']);
    $correo = pedidoPdfEscape($pedido['correo']);
    $cedula = pedidoPdfEscape($pedido['cedula'] ?? 'N/A');
    $styles = pedidoPdfStyles();

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comprobante de Pedido #{$idPedido}</title>
  <style>{$styles}</style>
</head>
<body>
  <main>
    <h1>Comprobante de Compra</h1>
    <p class="detail">Número de Pedido: {$idPedido}</p>
    <p class="detail">Nombre: {$cliente}</p>
    <p class="detail">Correo Electrónico: {$correo}</p>
    <p class="detail">Número de Cédula: {$cedula}</p>
    <p class="detail">Fecha: {$fecha}</p>
    <p class="detail">Método de pago: PayPal</p>
    <p class="detail">Estado del pedido: Procesado correctamente</p>
    <p class="section-title">Productos:</p>
    {$itemsHtml}
    <p class="detail total">Total: \${$total}</p>
  </main>
</body>
</html>
HTML;
}

function generatePedidoPdf(PDO $conn, int $id_pedido, string $outputPath): string
{
    $dompdf = new Dompdf\Dompdf(['isHtml5ParserEnabled' => true]);
    $dompdf->loadHtml(buildPedidoPdfHtml($conn, $id_pedido), 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    renderPedidoPdf($dompdf);

    $dir = dirname($outputPath);
    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        throw new RuntimeException('No se pudo crear el directorio del comprobante PDF.');
    }

    if (file_put_contents($outputPath, $dompdf->output()) === false) {
        throw new RuntimeException('No se pudo guardar el comprobante PDF.');
    }

    return $outputPath;
}
