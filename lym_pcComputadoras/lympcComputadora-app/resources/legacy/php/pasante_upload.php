<?php

use App\Services\GitHubRepositoryService;

session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'pasante') {
    header('Location: /php/login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? null;
$usuario = $_SESSION['usuario'] ?? 'Pasante';

if (!$id_usuario) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Usuario no identificado'));
    exit;
}

$uploadType = trim((string) ($_POST['upload_type'] ?? ''));
$documentTitle = trim((string) ($_POST['document_title'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$repoUrl = trim((string) ($_POST['repo_url'] ?? ''));
$githubBranch = trim((string) ($_POST['github_branch'] ?? ''));

if (strlen($documentTitle) > 180 || strlen($description) > 2000 || strlen($repoUrl) > 500 || strlen($githubBranch) > 255) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Los datos enviados superan el límite permitido'));
    exit;
}

$allowedProjectExtensions = ['zip', 'rar', 'tar', 'gz', 'pdf', 'docx', 'xlsx', 'pptx'];
$allowedReportExtensions = ['pdf'];

if (!isset($_FILES['documento']) || !is_array($_FILES['documento'])) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('No se cargó ningún archivo'));
    exit;
}

$archivo = $_FILES['documento'];
if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $mensaje = match ((int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE)) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido por el servidor',
        UPLOAD_ERR_NO_FILE => 'Selecciona un archivo para cargar',
        default => 'No se pudo cargar el archivo',
    };
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode($mensaje));
    exit;
}

$nombreOriginal = basename((string) ($archivo['name'] ?? ''));
$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
$githubService = null;

if ($uploadType === 'project') {
    if ($repoUrl === '') {
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Indica el repositorio de GitHub del proyecto'));
        exit;
    }

    try {
        $githubService = app(GitHubRepositoryService::class);
        $githubService->ensureConfigured();
        $githubService->parseRepositoryUrl($repoUrl);
    } catch (Throwable $exception) {
        $mensaje = $exception instanceof InvalidArgumentException
            ? $exception->getMessage()
            : 'No se pudo validar la conexión con GitHub';
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode($mensaje));
        exit;
    }

    if (!in_array($extension, $allowedProjectExtensions, true)) {
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Extensión de proyecto no permitida'));
        exit;
    }
    if (empty($documentTitle)) {
        $documentTitle = 'Proyecto sin título';
    }
} elseif ($uploadType === 'report') {
    if (!in_array($extension, $allowedReportExtensions, true)) {
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Solo se permiten archivos PDF para reportes'));
        exit;
    }
    if (empty($documentTitle)) {
        $documentTitle = 'Reporte sin título';
    }
} else {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Tipo de subida no válido'));
    exit;
}

$fileSize = filesize((string) ($archivo['tmp_name'] ?? ''));
if ($uploadType === 'project' && ($fileSize === false || $fileSize > (int) config('services.github.max_file_bytes', 52428800))) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('El archivo supera el tamaño máximo permitido para GitHub'));
    exit;
}

$uploadDirectory = storage_path('app/public/uploads/pasante/');
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('No se pudo preparar el almacenamiento del archivo'));
    exit;
}

try {
    $uniquePart = bin2hex(random_bytes(4));
} catch (Throwable $exception) {
    $uniquePart = (string) mt_rand(10000000, 99999999);
}
$targetName = sprintf('pasante_%s_%s_%s_%s.%s', $id_usuario, $uploadType, date('YmdHis'), $uniquePart, $extension);
$targetPath = $uploadDirectory . $targetName;

if (!move_uploaded_file($archivo['tmp_name'], $targetPath)) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('Error al mover el archivo cargado'));
    exit;
}

$githubData = [];
if ($uploadType === 'project') {
    try {
        $githubData = $githubService->publish($repoUrl, $targetPath, $targetName, $githubBranch !== '' ? $githubBranch : null);
    } catch (Throwable $exception) {
        @unlink($targetPath);
        error_log('No se pudo publicar el proyecto en GitHub: ' . $exception->getMessage());
        header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('No se pudo publicar el proyecto en GitHub. Verifica el repositorio, la rama y los permisos del token.'));
        exit;
    }
}

$metadataFile = $uploadDirectory . 'metadata.json';
$entries = [];
if (file_exists($metadataFile)) {
    $json = file_get_contents($metadataFile);
    $entries = json_decode($json, true) ?: [];
}

$entry = [
    'id' => uniqid('pasante_', true),
    'user_id' => $id_usuario,
    'user_name' => $usuario,
    'type' => $uploadType,
    'title' => $documentTitle,
    'description' => $description,
    'repo_url' => $repoUrl,
    'original_name' => $nombreOriginal,
    'filename' => $targetName,
    'path' => legacy_file_url('uploads/pasante/' . $targetName),
    'uploaded_at' => date('Y-m-d H:i:s'),
];

if ($uploadType === 'project') {
    $entry['github_url'] = $githubData['html_url'];
    $entry['github_path'] = $githubData['path'];
    $entry['github_branch'] = $githubData['branch'];
    $entry['github_commit'] = $githubData['commit_sha'];
}

$entries[] = $entry;

if (file_put_contents($metadataFile, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
    header('Location: /php/dashboard_pasante.php?upload=error&message=' . urlencode('El archivo se publicó, pero no se pudo actualizar el historial local'));
    exit;
}

header('Location: /php/dashboard_pasante.php?upload=success&type=' . urlencode($uploadType));
exit;

