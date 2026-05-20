<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
    $sql = "
        SELECT p.nombre, SUM(dp.cantidad) as total_vendido
        FROM detalles_pedido dp
        INNER JOIN productos p ON dp.id_producto = p.id_producto
        GROUP BY dp.id_producto
        ORDER BY total_vendido DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nombres = [];
    $cantidades = [];

    foreach ($resultados as $fila){
        $nombres[] = $fila['nombre'];
        $cantidades[] = $fila['total_vendido'];
    }

    echo json_encode([
        'success' => true, 
        'nombres' => $nombres,
        'cantidades' => $cantidades
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>