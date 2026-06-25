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
        LIMIT 20
    ";

    $sql1 = "
        SELECT pd.nombre, SUM(pd.unidades) as total_vendido1
        FROM productos pd
        GROUP BY pd.id_producto
        ORDER BY total_vendido1 DESC
        LIMIT 20
    ";

    $sql2 = "
        SELECT 
            CONCAT(nombre, ' (', correo, ')') as nombre, 
            COUNT(*) as total_enviado
        FROM contacto
        GROUP BY id_usuario, correo, nombre
        ORDER BY total_enviado DESC
        LIMIT 20
    ";

    $sql3 = "
        SELECT 
            CONCAT(nombre, ' (', correo, ')') as nombre, 
            COUNT(*) as total_enviado
        FROM citas
        GROUP BY id_usuario, correo, nombre
        ORDER BY total_enviado DESC
        LIMIT 20
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute();
    $resultados1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute();
    $resultados2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute();
    $resultados3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    $nombres = [];
    $cantidades = [];

    foreach ($resultados as $fila){
        $nombres[] = $fila['nombre'];
        $cantidades[] = $fila['total_vendido'];
    }

    $nombres1 = [];
    $cantidades1 = [];

    foreach ($resultados1 as $fila){
        $nombres1[] = $fila['nombre'];
        $cantidades1[] = $fila['total_vendido1'];
    }

    $nombres2 = [];
    $cantidades2 = [];
    foreach ($resultados2 as $fila){
        $nombres2[] = $fila['nombre'];
        $cantidades2[] = $fila['total_enviado'];
    }

    $nombres3 = [];
    $cantidades3 = [];
    foreach ($resultados3 as $fila){
        $nombres3[] = $fila['nombre'];
        $cantidades3[] = $fila['total_enviado'];
    }

    echo json_encode([
        'success' => true,
        'Grafica_venta_usu' => [
            'nombres' => $nombres,
            'cantidades' => $cantidades
        ],
        'Grafica_venta_pro' => [
            'nombres' => $nombres1,
            'cantidades' => $cantidades1
        ],
        'Grafica_contacto' => [
            'nombres' => $nombres2,
            'cantidades' => $cantidades2
        ],
        'Grafica_citas' => [
            'nombres' => $nombres3,
            'cantidades' => $cantidades3
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>


