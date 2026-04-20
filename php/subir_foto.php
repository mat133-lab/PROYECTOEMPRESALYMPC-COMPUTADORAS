<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['id_usuario']) && !isset($_SESSION['id'])) {
    header("Location: ../php/login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'];

if(isset($_FILES['foto-perfil']) && $_FILES['foto-perfil']['error'] == 0) {
    $directorio_destino = '../uploads/profile/';
    if(!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    $nombre_archivo = $_FILES['foto-perfil']['name'];
    $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
    //Generar un nombre unico para que no se sobreescriban fotos con el mismo nombre
    $nuevo_nombre = "perfil_" . $id_usuario . "_" . time() . "." . $extension;
    $ruta_final = $directorio_destino . $nuevo_nombre;
    if(move_uploaded_file($_FILES['foto-perfil']['tmp_name'], $ruta_final)){
        //actualiza el nombre de la foto en la base de datos
        $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id_usuario = ?");
        if($stmt->execute([$nuevo_nombre, $id_usuario])){
            //devolvemos al usuario a su perfil
            header("Location: ../php/perfilUsu.php?upload=success");
            exit();
        }
    }
}
header("Location: ../php/perfilUsu.php?error=subida_fallida");
exit();
?>