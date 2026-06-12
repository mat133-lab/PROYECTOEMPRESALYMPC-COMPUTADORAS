<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$stats = [
    'total_pedidos' => 0,
    'total_ventas' => 0,
    'producto_mas_vendido' => 'Sin ventas aún'
];

$stmtStats = $conn->query("SELECT COUNT(*) AS total_pedidos, COALESCE(SUM(total),0) AS total_ventas FROM pedidos");
$statsRow = $stmtStats->fetch(PDO::FETCH_ASSOC);
$stats['total_pedidos'] = (int)($statsRow['total_pedidos'] ?? 0);
$stats['total_ventas'] = (float)($statsRow['total_ventas'] ?? 0);

$stmtTop = $conn->query("SELECT pr.nombre, SUM(dp.cantidad) AS unidades FROM detalles_pedido dp JOIN productos pr ON pr.id_producto = dp.id_producto GROUP BY dp.id_producto ORDER BY unidades DESC LIMIT 1");
$top = $stmtTop->fetch(PDO::FETCH_ASSOC);
if ($top) {
    $stats['producto_mas_vendido'] = $top['nombre'] . ' (' . (int)$top['unidades'] . ' unidades)';
}

$stmtPedidos = $conn->query("SELECT p.id_pedido, p.fecha_pedido, p.total, u.usuario, u.correo, u.cedula FROM pedidos p JOIN usuarios u ON u.id_usuario = p.id_usuario ORDER BY p.id_pedido DESC");
$pedidos = $stmtPedidos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/pedidos.css">
</head>
<body>
<nav class="navbar navbar-dark bg-warning fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand mx-auto" href="dashboardadmin.php">L&M PC Computadoras</a>
    </div>
</nav>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h1 class="h3 mb-1">Gestión de Pedidos</h1>
                <p class="text-muted mb-0">Consulta compras, productos vendidos y genera comprobantes PDF para cada pedido.</p>
            </div>
            <a href="dashboardadmin.php" class="btn btn-outline-warning"><i class="fas fa-arrow-left"></i> Volver al panel</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="text-muted small">Total de pedidos</div>
                    <div class="display-6 fw-bold text-warning"><?php echo (int)$stats['total_pedidos']; ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="text-muted small">Ventas acumuladas</div>
                    <div class="display-6 fw-bold text-success">$<?php echo number_format($stats['total_ventas'], 2); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="text-muted small">Producto más vendido</div>
                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($stats['producto_mas_vendido']); ?></div>
                </div>
            </div>
        </div>

        <div class="table-wrap p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Historial de compras</h2>
                <span class="badge bg-warning text-dark badge-pill">Pedidos registrados</span>
            </div>
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th># Pedido</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Cédula</th>
                        <th>Correo</th>
                        <th>Total</th>
                        <th>Detalle</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No hay pedidos registrados aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                            <?php
                            $stmtItems = $conn->prepare("SELECT dp.cantidad, pr.nombre FROM detalles_pedido dp JOIN productos pr ON pr.id_producto = dp.id_producto WHERE dp.id_pedido = ? ORDER BY dp.id_detalle ASC");
                            $stmtItems->execute([$pedido['id_pedido']]);
                            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                            $detalle = '';
                            foreach ($items as $item) {
                                $detalle .= '<span class="badge bg-light text-dark me-1 mb-1">' . htmlspecialchars($item['nombre']) . ' × ' . (int)$item['cantidad'] . '</span>';
                            }
                            ?>
                            <tr>
                                <td><strong>#<?php echo (int)$pedido['id_pedido']; ?></strong></td>
                                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['usuario']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['cedula'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($pedido['correo']); ?></td>
                                <td>$<?php echo number_format((float)$pedido['total'], 2); ?></td>
                                <td><?php echo $detalle ?: '<span class="text-muted">Sin detalle</span>'; ?></td>
                                <td>
                                    <a href="generar_pedido_pdf.php?id=<?php echo (int)$pedido['id_pedido']; ?>" target="_blank" class="btn btn-sm btn-danger me-1"><i class="fas fa-file-pdf"></i> Ver</a>
                                    <a href="generar_pedido_pdf.php?id=<?php echo (int)$pedido['id_pedido']; ?>&download=1" class="btn btn-sm btn-outline-danger"><i class="fas fa-download"></i> PDF</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
