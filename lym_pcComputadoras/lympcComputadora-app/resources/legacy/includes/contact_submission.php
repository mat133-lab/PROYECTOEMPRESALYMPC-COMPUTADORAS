<?php

require_once __DIR__.'/file_helpers.php';

function legacyHandleContactSubmission(PDO $conn, string $returnPage): never
{
    $read = static function (string $key): string {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    };
    $length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

    $name = $read('name');
    $email = $read('email');
    $cedula = $read('cedula');
    $company = $read('company');
    $message = $read('message');

    if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)
        || $cedula === '' || ! preg_match('/^[0-9]{6,20}$/', $cedula)
        || $message === '' || $length($name) > 255 || $length($email) > 255
        || $length($company) > 255 || $length($message) > 10000) {
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
        if ($rucPath !== null) {
            $storedFiles[] = storage_path('app/public/'.$rucPath);
        }
        $cedulaPath = legacy_store_upload($_FILES['archivo_cedula'] ?? [], 'docs', 'cedula', $allowedTypes);
        if ($cedulaPath !== null) {
            $storedFiles[] = storage_path('app/public/'.$cedulaPath);
        }

        $stmt = $conn->prepare('INSERT INTO contacto (id_usuario, nombre, correo, compania, mensaje, cedula, archivo_ruc, archivo_cedula) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) ($_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0) ?: null,
            $name,
            $email,
            $company,
            $message,
            $cedula,
            $rucPath,
            $cedulaPath,
        ]);

        header('Location: '.$returnPage.'?status=success');
        exit;
    } catch (Throwable $exception) {
        foreach ($storedFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        error_log('No se pudo guardar el formulario de contacto: '.$exception->getMessage());
        $reason = $exception instanceof RuntimeException ? 'file' : 'save';
        header('Location: '.$returnPage.'?status=error&reason='.$reason);
        exit;
    }
}
