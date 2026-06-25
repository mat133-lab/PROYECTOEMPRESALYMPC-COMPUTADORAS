<?php
session_start();
include_once '../includes/db.php';

if(!isset($_SESSION['usuario'])){
    header("Location: login.php");
    exit();
}

//Para Actualizar
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id_producto = $_POST['id_producto']; 
    $nombre      = $_POST['nombre'];
    $serie       = $_POST['serie'];
    $fecha       = $_POST['fecha'];
    $unidades    = $_POST['unidades'];
    $precio      = $_POST['precio'];
    $categoria   = $_POST['categoria'];
    
    $query = "UPDATE productos SET nombre = ?, serie = ?, fecha = ?, unidades = ?, precio = ?, categoria = ?";
    $params = [$nombre, $serie, $fecha, $unidades, $precio, $categoria];

    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK){
        
        $nombre_imagen = $_FILES['imagen']['name'];
        $ruta_temporal = $_FILES['imagen']['tmp_name'];
        $carpeta_destino = '../uploads/'; 
        
        if(move_uploaded_file($ruta_temporal, $carpeta_destino . $nombre_imagen)){
            $query .= ", imagen = ?";
            $params[] = $nombre_imagen;
        }
    }

    $query .= " WHERE id_producto = ?";
    $params[] = $id_producto;

    try {
        $stmt = $conn->prepare($query);

        if($stmt->execute($params)){
            header("Location: /php/productos.php?mensaje=actualizado_exito");
            exit();
        }else{
            echo "No se ha podido cambiar los datos";
        }

    } catch (PDOException $e) {
        die("Error en la base de datos. " . $e->getMessage());
    }
}else{
    header("Location: asus.php");
    exit();
}
?>