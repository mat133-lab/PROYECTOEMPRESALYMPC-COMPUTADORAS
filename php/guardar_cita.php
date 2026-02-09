<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../php/login.php");
    exit();
}

// Usar SOLO usuario como nombre
$nombre   = $_SESSION['usuario'];
$apellido = $_POST['apellido'] ;
$correo   = $_POST['correo'];
$telefono = $_POST['telefono'];
$fecha   = $_POST['fecha'];
$motivo  = $_POST['motivo'];

if (!$fecha || !$motivo) {
    header("Location: ../php/horario.php?error=datos");
    exit();
}

$sql = "INSERT INTO citas (nombre, apellido, correo, fecha, telefono, motivo)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->execute([$nombre,$apellido,$correo,$fecha,$telefono,$motivo]);

header("Location: ../php/horario.php?ok=1");
exit();