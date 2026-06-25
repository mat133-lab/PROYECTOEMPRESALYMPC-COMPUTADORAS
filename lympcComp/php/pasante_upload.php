<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'pasante') {
    header('Location: /php/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
$usuario = $_SESSION['usuario'] ?? 'Pasante';

if (!$id_usuario) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Usuario no identificado'));
    exit();
}

$uploadType = $_POST['upload_type'] ?? ''; 
$documentTitle = trim($_POST['document_title'] ?? '');
$description = trim($_POST['description'] ?? '');
$repoUrl = trim($_POST['repo_url'] ?? '');

$allowedProjectExtensions = ['zip', 'rar', 'tar', 'gz', 'pdf', 'docx', 'xlsx', 'pptx'];
$allowedReportExtensions = ['pdf'];

if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('No se cargó ningún archivo válido'));
    exit();
}

$archivo = $_FILES['documento'];
$nombreOriginal = basename($archivo['name']);
$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

if ($uploadType === 'project') {
    if (!in_array($extension, $allowedProjectExtensions, true)) {
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Extensión de proyecto no permitida'));
        exit();
    }
    if (empty($documentTitle)) {
        $documentTitle = 'Proyecto sin título';
    }
} elseif ($uploadType === 'report') {
    if (!in_array($extension, $allowedReportExtensions, true)) {
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Solo se permiten archivos PDF para reportes'));
        exit();
    }
    if (empty($documentTitle)) {
        $documentTitle = 'Reporte sin título';
    }
} else {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Tipo de subida no válido'));
    exit();
}

$uploadDirectory = __DIR__ . '/../uploads/pasante/';
if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

$cleanName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', pathinfo($nombreOriginal, PATHINFO_FILENAME));
$targetName = sprintf('pasante_%s_%s_%s.%s', $id_usuario, $uploadType, time(), $extension);
$targetPath = $uploadDirectory . $targetName;

if (!move_uploaded_file($archivo['tmp_name'], $targetPath)) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Error al mover el archivo cargado'));
    exit();
}

$metadataFile = $uploadDirectory . 'metadata.json';
$entries = [];
if (file_exists($metadataFile)) {
    $json = file_get_contents($metadataFile);
    $entries = json_decode($json, true) ?: [];
}

$entries[] = [
    'id' => uniqid('pasante_', true),
    'user_id' => $id_usuario,
    'user_name' => $usuario,
    'type' => $uploadType,
    'title' => $documentTitle,
    'description' => $description,
    'repo_url' => $repoUrl,
    'original_name' => $nombreOriginal,
    'filename' => $targetName,
    'path' => '../uploads/pasante/' . $targetName,
    'uploaded_at' => date('Y-m-d H:i:s'),
];

file_put_contents($metadataFile, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

header('Location: /php/dashboard_pasante.php?upload=success&type=' . urlencode($uploadType));
exit();
