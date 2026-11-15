<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function horariosAdminJson(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function horariosAdminValidDate(mixed $value): ?string
{
    if (! is_string($value)) {
        return null;
    }

    $value = trim($value);
    // The table stores DATE values. Accept older datetime-local clients but
    // persist only the calendar date so all calendar views use one format.
    if (! preg_match('/^(\d{4}-\d{2}-\d{2})(?:T\d{2}:\d{2}(?::\d{2})?)?$/', $value, $matches)) {
        return null;
    }

    [$year, $month, $day] = array_map('intval', explode('-', $matches[1]));

    return checkdate($month, $day, $year) ? $matches[1] : null;
}

function horariosAdminPayload(mixed $data): array
{
    if (! is_array($data)) {
        horariosAdminJson(['success' => false, 'error' => 'El cuerpo de la solicitud no es válido.'], 400);
    }

    $payload = [
        'nombre' => trim((string) ($data['nombre'] ?? '')),
        'apellido' => trim((string) ($data['apellido'] ?? '')),
        'correo' => trim((string) ($data['correo'] ?? '')),
        'cedula' => trim((string) ($data['cedula'] ?? '')),
        'fecha' => horariosAdminValidDate($data['fecha'] ?? null),
        'telefono' => trim((string) ($data['telefono'] ?? '')),
        'motivo' => trim((string) ($data['motivo'] ?? '')),
    ];
    $length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

    if (in_array('', [$payload['nombre'], $payload['apellido'], $payload['correo'], $payload['telefono'], $payload['motivo']], true)
        || $payload['fecha'] === null
        || ! filter_var($payload['correo'], FILTER_VALIDATE_EMAIL)
        || $length($payload['nombre']) > 255
        || $length($payload['apellido']) > 255
        || $length($payload['correo']) > 255
        || ($payload['cedula'] !== '' && ! preg_match('/^[0-9]{6,20}$/', $payload['cedula']))
        || $length($payload['telefono']) > 25
        || $length($payload['motivo']) > 500) {
        horariosAdminJson([
            'success' => false,
            'error' => 'Completa los datos requeridos y verifica que la cédula tenga entre 6 y 20 dígitos.',
        ], 422);
    }

    return $payload;
}

function horariosAdminFileUrls(array $appointment): array
{
    $appointment['archivo_ruc'] = legacy_file_url($appointment['archivo_ruc'] ?? null);
    $appointment['archivo_cedula'] = legacy_file_url($appointment['archivo_cedula'] ?? null);

    return $appointment;
}

$rolesPermitidos = ['admin', 'tecnico', 'encargado', 'pasante'];
$rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
if (! isset($_SESSION['usuario'])) {
    horariosAdminJson(['success' => false, 'error' => 'Inicia sesión para continuar.'], 401);
}
if (! in_array($rol, $rolesPermitidos, true)) {
    horariosAdminJson(['success' => false, 'error' => 'No tienes permiso para administrar citas.'], 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$uploadedFiles = [];

try {
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (! $id) {
                horariosAdminJson(['success' => false, 'error' => 'El ID de la cita no es válido.'], 400);
            }

            $stmt = $conn->prepare('SELECT id_cita AS id, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo FROM citas WHERE id_cita = ? LIMIT 1');
            $stmt->execute([$id]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            if (! $appointment) {
                horariosAdminJson(['success' => false, 'error' => 'No se encontró la cita solicitada.'], 404);
            }

            horariosAdminJson(horariosAdminFileUrls($appointment));
        }

        if (isset($_GET['ids'])) {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', explode(',', (string) $_GET['ids'])),
                static fn (int $id): bool => $id > 0
            )));
            if ($ids === []) {
                horariosAdminJson([]);
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT id_cita AS id, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo FROM citas WHERE id_cita IN ($placeholders) ORDER BY fecha ASC, id_cita ASC");
            $stmt->execute($ids);
            horariosAdminJson(array_map('horariosAdminFileUrls', $stmt->fetchAll(PDO::FETCH_ASSOC)));
        }

        if (($_GET['upcoming'] ?? '') === '1') {
            $stmt = $conn->query('SELECT id_cita AS id, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo FROM citas WHERE fecha >= CURDATE() ORDER BY fecha ASC, id_cita ASC LIMIT 15');
            horariosAdminJson(array_map('horariosAdminFileUrls', $stmt->fetchAll(PDO::FETCH_ASSOC)));
        }

        $desde = horariosAdminValidDate($_GET['desde'] ?? date('Y-m-01'));
        $hasta = horariosAdminValidDate($_GET['hasta'] ?? date('Y-m-t'));
        if ($desde === null || $hasta === null || $desde > $hasta) {
            horariosAdminJson(['success' => false, 'error' => 'El rango de fechas no es válido.'], 422);
        }

        $stmt = $conn->prepare('SELECT id_cita AS id, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo FROM citas WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC, id_cita ASC');
        $stmt->execute([$desde, $hasta]);
        horariosAdminJson(array_map('horariosAdminFileUrls', $stmt->fetchAll(PDO::FETCH_ASSOC)));
    }

    if ($method === 'POST' || $method === 'PUT') {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $isFormPost = $method === 'POST'
            && (str_starts_with($contentType, 'multipart/form-data')
                || str_starts_with($contentType, 'application/x-www-form-urlencoded'));
        if ($isFormPost) {
            $data = $_POST;
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                horariosAdminJson(['success' => false, 'error' => 'El JSON enviado no es válido.'], 400);
            }
        }

        $payload = horariosAdminPayload($data);
        $isUpdate = $method === 'PUT' || trim((string) ($data['id'] ?? '')) !== '';
        $existing = null;
        $id = null;
        if ($isUpdate) {
            $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (! $id) {
                horariosAdminJson(['success' => false, 'error' => 'El ID de la cita no es válido.'], 400);
            }

            $stmt = $conn->prepare('SELECT cedula, archivo_ruc, archivo_cedula FROM citas WHERE id_cita = ? LIMIT 1');
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if (! $existing) {
                horariosAdminJson(['success' => false, 'error' => 'No se encontró la cita que deseas actualizar.'], 404);
            }
        }

        $allowedTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        try {
            $rucPath = legacy_store_upload($_FILES['archivo_ruc'] ?? [], 'docs', 'ruc', $allowedTypes);
            if ($rucPath !== null) {
                $uploadedFiles[] = $rucPath;
            }
            $cedulaPath = legacy_store_upload($_FILES['archivo_cedula'] ?? [], 'docs', 'cedula', $allowedTypes);
            if ($cedulaPath !== null) {
                $uploadedFiles[] = $cedulaPath;
            }
        } catch (RuntimeException $exception) {
            foreach ($uploadedFiles as $uploadedFile) {
                $path = storage_path('app/public/'.$uploadedFile);
                if (is_file($path) && ! unlink($path)) {
                    error_log('No se pudo limpiar un archivo temporal de la cita: '.$path);
                }
            }
            horariosAdminJson(['success' => false, 'error' => $exception->getMessage()], 422);
        }

        $cedula = array_key_exists('cedula', $data)
            ? ($payload['cedula'] === '' ? null : $payload['cedula'])
            : ($existing['cedula'] ?? null);
        $rucPath ??= $existing['archivo_ruc'] ?? null;
        $cedulaPath ??= $existing['archivo_cedula'] ?? null;

        if (! $isUpdate) {
            $stmt = $conn->prepare('INSERT INTO citas (nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $payload['nombre'],
                $payload['apellido'],
                $payload['correo'],
                $cedula,
                $rucPath,
                $cedulaPath,
                $payload['fecha'],
                $payload['telefono'],
                $payload['motivo'],
            ]);
            horariosAdminJson(['success' => true, 'id' => (int) $conn->lastInsertId(), 'message' => 'Cita creada correctamente.'], 201);
        }

        $stmt = $conn->prepare('UPDATE citas SET nombre = ?, apellido = ?, correo = ?, cedula = ?, archivo_ruc = ?, archivo_cedula = ?, fecha = ?, telefono = ?, motivo = ? WHERE id_cita = ?');
        $stmt->execute([
            $payload['nombre'],
            $payload['apellido'],
            $payload['correo'],
            $cedula,
            $rucPath,
            $cedulaPath,
            $payload['fecha'],
            $payload['telefono'],
            $payload['motivo'],
            $id,
        ]);

        horariosAdminJson(['success' => true, 'message' => 'Cita actualizada correctamente.']);
    }

    if ($method === 'DELETE') {
        $data = [];
        if (! empty($_GET['id'])) {
            $data['id'] = $_GET['id'];
        } else {
            $rawBody = file_get_contents('php://input');
            if ($rawBody !== '') {
                $data = json_decode($rawBody, true) ?? [];
            }
        }

        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (! $id) {
            horariosAdminJson(['success' => false, 'error' => 'El ID de la cita no es válido.'], 400);
        }

        $stmt = $conn->prepare('DELETE FROM citas WHERE id_cita = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) {
            horariosAdminJson(['success' => false, 'error' => 'No se encontró la cita que deseas eliminar.'], 404);
        }

        horariosAdminJson(['success' => true, 'message' => 'Cita eliminada correctamente.']);
    }

    header('Allow: GET, POST, PUT, DELETE');
    horariosAdminJson(['success' => false, 'error' => 'Método no permitido.'], 405);
} catch (Throwable $exception) {
    foreach ($uploadedFiles as $uploadedFile) {
        $path = storage_path('app/public/'.$uploadedFile);
        if (is_file($path) && ! unlink($path)) {
            error_log('No se pudo limpiar un archivo temporal de la cita: '.$path);
        }
    }

    error_log('Error en la API de horarios: '.$exception->getMessage());
    horariosAdminJson(['success' => false, 'error' => 'No se pudo completar la operación de citas.'], 500);
}
