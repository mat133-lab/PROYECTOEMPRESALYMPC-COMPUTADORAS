<?php

require_once __DIR__.'/file_helpers.php';

function legacyHandleManualAppointment(PDO $conn, string $returnPage): never
{
    $read = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $name = $read('name');
    $lastName = $read('lastname');
    $email = $read('email');
    $identity = $read('cedula');
    $date = $read('date');
    $phone = $read('cell');
    $reason = $read('reason');
    $length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

    $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    if ($validDate) {
        [$year, $month, $day] = array_map('intval', explode('-', $date));
        $validDate = checkdate($month, $day, $year);
    }

    if ($name === '' || $lastName === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)
        || $length($name) > 255 || $length($lastName) > 255 || $length($email) > 255
        || ! preg_match('/^[0-9]{6,20}$/', $identity) || ! $validDate || $phone === ''
        || $length($phone) > 25 || $reason === '' || $length($reason) > 500) {
        header('Location: '.$returnPage.'?status=error&reason=data');
        exit;
    }

    $allowedTypes = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $storedFiles = [];

    try {
        $rucPath = legacy_store_upload($_FILES['archivo_ruc'] ?? [], 'docs', 'ruc', $allowedTypes);
        if ($rucPath !== null) $storedFiles[] = storage_path('app/public/'.$rucPath);
        $identityPath = legacy_store_upload($_FILES['archivo_cedula'] ?? [], 'docs', 'cedula', $allowedTypes);
        if ($identityPath !== null) $storedFiles[] = storage_path('app/public/'.$identityPath);

        $stmt = $conn->prepare('INSERT INTO citas (id_usuario, nombre, apellido, cedula, correo, fecha, telefono, motivo, accion_sensible, archivo_ruc, archivo_cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)');
        $stmt->execute([
            (int) ($_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0) ?: null,
            $name,
            $lastName,
            $identity,
            $email,
            $date,
            $phone,
            $reason,
            $rucPath,
            $identityPath,
        ]);

        header('Location: '.$returnPage.'?status=success');
        exit;
    } catch (Throwable $exception) {
        foreach ($storedFiles as $path) {
            if (is_file($path)) @unlink($path);
        }
        error_log('No se pudo guardar la cita manual: '.$exception->getMessage());
        $reasonCode = $exception instanceof RuntimeException ? 'file' : 'save';
        header('Location: '.$returnPage.'?status=error&reason='.$reasonCode);
        exit;
    }
}
