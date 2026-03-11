<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../php/login.php");
    exit();
}

// Usar id_usuario de la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre     = $_SESSION['usuario'];
$apellido  = $_POST['apellido'] ?? '';
$correo    = $_POST['correo'] ?? $_SESSION['correo'];
$telefono  = $_POST['telefono'];
$fecha     = $_POST['fecha'];
$motivo    = $_POST['motivo'];

if (!$fecha || !$motivo) {
    header("Location: ../php/horario.php?error=datos");
    exit();
}

// Insertar cita con id_usuario
$sql = "INSERT INTO citas (id_usuario, nombre, apellido, correo, fecha, telefono, motivo)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$id_usuario, $nombre, $apellido, $correo, $fecha, $telefono, $motivo]);

header("Location: ../php/horario.php?ok=1");
exit();
