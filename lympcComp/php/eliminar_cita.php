<?php
session_start();
require_once '../includes/db.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener ID de la cita
$id_cita = isset($_POST['id_cita']) ? trim($_POST['id_cita']) : '';
$id_cita = ctype_digit($id_cita) ? (int)$id_cita : 0;
if ($id_cita <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de cita inválido']);
    exit();
}

try {
    // Eliminar la cita solo si pertenece al usuario logueado
    $stmt = $conn->prepare("DELETE FROM citas WHERE id_cita = ? AND id_usuario = ?");
    $stmt->execute([$id_cita, $_SESSION['id_usuario']]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Cita no encontrada o no autorizada']);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Cita eliminada correctamente']);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?></content>
<parameter name="filePath">c:\xampp\htdocs\lymPCComputadoras\php\eliminar_cita.php