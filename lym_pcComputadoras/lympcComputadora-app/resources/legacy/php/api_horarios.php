<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function horariosUserJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function horariosUserDate(mixed $value): ?string
{
    if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value))) {
        return null;
    }

    [$year, $month, $day] = array_map('intval', explode('-', trim($value)));

    return checkdate($month, $day, $year) ? trim($value) : null;
}

if (! isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    horariosUserJson(['error' => 'Inicia sesión para consultar tus citas.'], 401);
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    horariosUserJson(['error' => 'Método no permitido.'], 405);
}

$desde = horariosUserDate($_GET['desde'] ?? date('Y-m-01'));
$hasta = horariosUserDate($_GET['hasta'] ?? date('Y-m-t'));
if ($desde === null || $hasta === null || $desde > $hasta) {
    horariosUserJson(['error' => 'El rango de fechas no es válido.'], 422);
}

try {
    $stmt = $conn->prepare("
        SELECT
            c.id_cita AS id,
            c.nombre,
            c.apellido,
            c.correo,
            c.cedula,
            c.archivo_ruc,
            c.archivo_cedula,
            c.fecha,
            c.telefono,
            c.motivo,
            u.usuario AS nombre_usuario,
            u.correo AS correo_usuario
        FROM citas c
        LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
        WHERE c.id_usuario = ? AND c.fecha BETWEEN ? AND ?
        ORDER BY c.fecha ASC, c.id_cita ASC
    ");
    $stmt->execute([(int) $_SESSION['id_usuario'], $desde, $hasta]);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($citas as &$cita) {
        $cita['archivo_ruc'] = legacy_file_url($cita['archivo_ruc'] ?? null);
        $cita['archivo_cedula'] = legacy_file_url($cita['archivo_cedula'] ?? null);
    }
    unset($cita);

    horariosUserJson($citas);
} catch (Throwable $exception) {
    error_log('Error al consultar citas del usuario: '.$exception->getMessage());
    horariosUserJson(['error' => 'No se pudieron cargar tus citas. Inténtalo de nuevo.'], 500);
}
