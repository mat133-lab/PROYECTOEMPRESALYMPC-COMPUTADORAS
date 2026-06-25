<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: /php/login.php");
    exit;
}

if(isset($_GET['id'])){
    $id_producto = $_GET['id'];
    try {
        $query = "DELETE FROM productos WHERE id_producto = :id";
        $stmt = $conn->prepare($query);
        // Asignamos el ID a la consulta
        $stmt->bindParam(':id', $id_producto);
        //Ejecucion
        if ($stmt->execute()){
            header("Location: /php/productos.php?mensaje=borrado_exito");
            exit;
        }else{
            echo "No se pudo borrar el producto";
        }
    } catch (PDOException $e) {
        throw new RuntimeException((string) ("Error en la base de datos: " . $e->getMessage()));
    }
}else {
    // Si entran al archivo sin un ID, los regresamos
    header("Location: /php/dashboard.php");
    exit;
}
?>


