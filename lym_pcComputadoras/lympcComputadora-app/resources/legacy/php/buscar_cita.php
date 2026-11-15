<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (! isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Inicia sesión para buscar citas.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$termino = trim((string) ($_GET['q'] ?? ''));
if ($termino === '') {
    echo json_encode([]);
    exit;
}
if ((function_exists('mb_strlen') ? mb_strlen($termino, 'UTF-8') : strlen($termino)) > 120) {
    http_response_code(422);
    echo json_encode(['error' => 'El término de búsqueda es demasiado largo.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rolesGlobales = ['admin', 'tecnico', 'encargado', 'pasante', 'asistente'];
$rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
$busqueda = '%'.$termino.'%';
$scopeSql = '';
$parameters = [$busqueda, $busqueda, $busqueda, $busqueda];
if (! in_array($rol, $rolesGlobales, true)) {
    $scopeSql = ' AND id_usuario = ?';
    $parameters[] = (int) $_SESSION['id_usuario'];
}

try {
    $stmt = $conn->prepare("
        SELECT id_cita AS id, nombre, apellido, correo, telefono, motivo, fecha
        FROM citas
        WHERE (nombre LIKE ? OR apellido LIKE ? OR motivo LIKE ? OR fecha LIKE ?){$scopeSql}
        ORDER BY fecha ASC, id_cita ASC
        LIMIT 50
    ");
    $stmt->execute($parameters);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    error_log('Error al buscar citas: '.$exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo completar la búsqueda.'], JSON_UNESCAPED_UNICODE);
}
