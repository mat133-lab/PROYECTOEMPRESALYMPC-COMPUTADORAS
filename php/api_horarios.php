<?php
session_start();
require_once '../includes/db.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Obtener parámetros de fecha
$desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
$hasta = isset($_GET['hasta']) ? $_GET['hasta'] . ' 23:59:59' : date('Y-m-t 23:59:59');

try {
    // Consultar citas del usuario en el rango de fechas
    $stmt = $conn->prepare("
        SELECT 
            id_cita as id,
            nombre,
            apellido,
            correo,
            fecha,
            telefono,
            motivo
        FROM citas 
        WHERE correo = ? AND fecha BETWEEN ? AND ?
        ORDER BY fecha ASC
    ");
    $stmt->execute([$_SESSION['correo'], $desde, $hasta]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retornar JSON
    header('Content-Type: application/json');
    echo json_encode($citas);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
