<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/file_helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    header('Location: ../php/perfilUsu.php?error=metodo');
    exit;
}

if (! isset($_SESSION['usuario']) || ! isset($_SESSION['id_usuario'])) {
    header('Location: ../php/login.php');
    exit;
}

$file = $_FILES['foto-perfil'] ?? [];
$storedPath = null;
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

try {
    $storedPath = legacy_store_upload($file, 'uploads/profile', 'perfil_'.(int) $_SESSION['id_usuario'], $allowedTypes, 5 * 1024 * 1024);
    if ($storedPath === null) {
        throw new RuntimeException('Selecciona una imagen para continuar.');
    }

    $stmt = $conn->prepare('UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?');
    $stmt->execute([basename($storedPath), (int) $_SESSION['id_usuario']]);
    if ($stmt->rowCount() === 0) {
        $check = $conn->prepare('SELECT 1 FROM usuarios WHERE id_usuario = ?');
        $check->execute([(int) $_SESSION['id_usuario']]);
        if (! $check->fetchColumn()) {
            throw new RuntimeException('No se encontró el perfil que deseas actualizar.');
        }
    }

    header('Location: ../php/perfilUsu.php?upload=success');
    exit;
} catch (Throwable $exception) {
    if ($storedPath !== null) {
        $fullPath = storage_path('app/public/'.$storedPath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    error_log('No se pudo actualizar la foto de perfil: '.$exception->getMessage());
    $reason = $exception instanceof RuntimeException ? 'archivo' : 'guardar';
    header('Location: ../php/perfilUsu.php?error='.$reason);
    exit;
}
