<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /php/login.php");
    exit;
}

// Usar id_usuario de la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre     = $_SESSION['usuario'];
$apellido  = $_POST['apellido'] ?? '';
$correo    = $_POST['correo'] ?? $_SESSION['correo'];
$cedula = $_POST['cedula'] ?? '';
$ruta_ruc = null;
$ruta_cedula = null;
$directorio_subida = storage_path('app/public/docs/');
if (!is_dir($directorio_subida)) {
    mkdir($directorio_subida, 0755, true);
}
if (isset($_FILES['archivo_ruc']) && $_FILES['archivo_ruc']['error'] === UPLOAD_ERR_OK) {
    $nombre_ruc = time() . "_ruc_" . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['archivo_ruc']['name']));

    $destino_ruc = $directorio_subida . $nombre_ruc;

    if (move_uploaded_file($_FILES['archivo_ruc']['tmp_name'], $destino_ruc)) {

        $ruta_ruc = 'docs/' . $nombre_ruc;
    }
}
if (isset($_FILES['archivo_cedula']) && $_FILES['archivo_cedula']['error'] === UPLOAD_ERR_OK) {
    $nombre_cedula = time() . "_cedula_" . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['archivo_cedula']['name']));

    $destino_cedula = $directorio_subida . $nombre_cedula;

    if (move_uploaded_file($_FILES['archivo_cedula']['tmp_name'], $destino_cedula)) {

        $ruta_cedula = 'docs/' . $nombre_cedula;
    }
}
$telefono  = $_POST['telefono'];
$fecha     = $_POST['fecha'];
$motivo    = $_POST['motivo'];

if (!$fecha || !$motivo) {
    header("Location: /php/horario.php?error=datos");
    exit;
}

// Insertar cita con id_usuario
$sql = "INSERT INTO citas (id_usuario, nombre, apellido, correo, cedula, archivo_ruc, archivo_cedula, fecha, telefono, motivo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario, $nombre, $apellido, $correo, $cedula, $ruta_ruc, $ruta_cedula, $fecha, $telefono, $motivo]);

header("Location: /php/horario.php?ok=1");
exit;


