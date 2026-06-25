<?php
session_start();
require_once '../includes/db.php';

// Permitir solo usuarios con rol de administración/servicio
$rolesConAcceso = ['admin', 'tecnico', 'encargado', 'pasante'];
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $rolesConAcceso)) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // GET: listar por rango de fechas o por ids
        if (!empty($_GET['ids'])) {
            // ids separados por comas
            $ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
            if (count($ids) === 0) {
                echo json_encode([]);
                exit();
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT id_cita as id, nombre, apellido, correo, fecha, telefono, motivo FROM citas WHERE id_cita IN ($placeholders) ORDER BY fecha ASC");
            $stmt->execute($ids);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
            exit();
        }

        $desde = isset($_GET['desde']) ? $_GET['desde'] : date('Y-m-01');
        $hasta = isset($_GET['hasta']) ? $_GET['hasta'] : date('Y-m-t');

        $stmt = $conn->prepare("SELECT id_cita as id, nombre, apellido, correo, fecha, telefono, motivo FROM citas WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC");
        $stmt->execute([$desde, $hasta]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
        exit();

    } elseif ($method === 'POST') {
        // Crear cita (JSON body)
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $correo = $data['correo'] ?? '';
        $fecha = $data['fecha'] ?? '';
        $telefono = $data['telefono'] ?? '';
        $motivo = $data['motivo'] ?? '';

        $stmt = $conn->prepare("INSERT INTO citas (nombre, apellido, correo, fecha, telefono, motivo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $correo, $fecha, $telefono, $motivo]);
        $id = $conn->lastInsertId();
        echo json_encode(['success' => true, 'id' => $id]);
        exit();

    } elseif ($method === 'PUT') {
        // Actualizar cita
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $correo = $data['correo'] ?? '';
        $fecha = $data['fecha'] ?? '';
        $telefono = $data['telefono'] ?? '';
        $motivo = $data['motivo'] ?? '';

        $stmt = $conn->prepare("UPDATE citas SET nombre = ?, apellido = ?, correo = ?, fecha = ?, telefono = ?, motivo = ? WHERE id_cita = ?");
        $stmt->execute([$nombre, $apellido, $correo, $fecha, $telefono, $motivo, $id]);
        echo json_encode(['success' => true]);
        exit();

    } elseif ($method === 'DELETE') {
        // Eliminar cita por id (query param id o JSON body)
        $id = 0;
        if (!empty($_GET['id'])) $id = intval($_GET['id']);
        else {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!empty($data['id'])) $id = intval($data['id']);
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM citas WHERE id_cita = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit();
    }

    // Método no soportado
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

?>
