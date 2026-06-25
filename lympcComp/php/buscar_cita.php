<?php
session_start();
require_once '../includes/db.php';
if(!isset($_SESSION['usuario'])){
http_response_code(403);
echo json_encode(['error'=>'No autorizado']);
exit();
}
$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
if (empty($termino)){
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}
try {
    $sql = "SELECT id_cita as id, nombre, apellido, correo, telefono, motivo, fecha 
            FROM citas
            WHERE nombre LIKE :busqueda
            OR apellido LIKE :busqueda
            OR motivo LIKE :busqueda
            OR fecha LIKE :busqueda
            ORDER BY fecha ASC
            ";
    $stmt = $conn->prepare($sql);
    $busquedaConComodines = '%' . $termino . '%';
    $stmt->bindParam(':busqueda', $busquedaConComodines, PDO::PARAM_STR);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error'=> 'Error al consultar a la base de datos' . $e->getMessage()]);
};
?>