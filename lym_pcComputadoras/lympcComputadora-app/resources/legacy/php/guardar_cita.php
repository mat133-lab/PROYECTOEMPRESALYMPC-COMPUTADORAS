<?php
session_start();
require_once '../includes/db.php';

function citaRedirect(string $result): never
{
    header('Location: ../php/horario.php?'.$result);
    exit;
}

function citaUpload(string $field, string $prefix): ?string
{
    if (! isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ! is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('No se pudo recibir uno de los archivos adjuntos.');
    }
    if (($file['size'] ?? 0) < 1 || $file['size'] > 10 * 1024 * 1024) {
        throw new RuntimeException('Cada archivo debe pesar menos de 10 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (! isset($extensions[$mime])) {
        throw new RuntimeException('Adjunta documentos PDF o imágenes JPG, PNG o WebP.');
    }

    $directory = storage_path('app/public/docs');
    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento de documentos.');
    }

    $fileName = $prefix.'_'.bin2hex(random_bytes(12)).'.'.$extensions[$mime];
    if (! move_uploaded_file($file['tmp_name'], $directory.DIRECTORY_SEPARATOR.$fileName)) {
        throw new RuntimeException('No se pudo guardar uno de los archivos adjuntos.');
    }

    return 'docs/'.$fileName;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    citaRedirect('error=metodo');
}
if (! isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    header('Location: ../php/login.php');
    exit;
}

$apellido = trim((string) ($_POST['apellido'] ?? ''));
$correo = trim((string) ($_SESSION['correo'] ?? ''));
$cedula = trim((string) ($_POST['cedula'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$fecha = trim((string) ($_POST['fecha'] ?? ''));
$motivo = trim((string) ($_POST['motivo'] ?? ''));
$fechaValida = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1;
if ($fechaValida) {
    [$year, $month, $day] = array_map('intval', explode('-', $fecha));
    $fechaValida = checkdate($month, $day, $year);
}

$length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
if ($apellido === '' || $correo === '' || ! filter_var($correo, FILTER_VALIDATE_EMAIL)
    || $cedula === '' || $telefono === '' || $motivo === '' || ! $fechaValida
    || $length($apellido) > 255 || $length($cedula) > 20 || $length($telefono) > 25 || $length($motivo) > 500) {
    citaRedirect('error=datos');
}

$uploadedPaths = [];
try {
    $rutaRuc = citaUpload('archivo_ruc', 'ruc');
    if ($rutaRuc !== null) {
        $uploadedPaths[] = storage_path('app/public/'.$rutaRuc);
    }
    $rutaCedula = citaUpload('archivo_cedula', 'cedula');
    if ($rutaCedula !== null) {
        $uploadedPaths[] = storage_path('app/public/'.$rutaCedula);
    }

    $stmt = $conn->prepare('INSERT INTO citas (id_usuario, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        (int) $_SESSION['id_usuario'],
        trim((string) $_SESSION['usuario']),
        $apellido,
        $correo,
        $cedula,
        $rutaRuc,
        $rutaCedula,
        $fecha,
        $telefono,
        $motivo,
    ]);
    citaRedirect('ok=1');
} catch (Throwable $exception) {
    foreach ($uploadedPaths as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }
    error_log('Error al guardar cita: '.$exception->getMessage());
    citaRedirect($exception instanceof RuntimeException ? 'error=archivo' : 'error=guardar');
}
