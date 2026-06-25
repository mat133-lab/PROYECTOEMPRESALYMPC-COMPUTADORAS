<?php
session_start();
include_once '../includes/db.php';

// 1. Validar que el usuario sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    // Si no es admin, lo mandamos de vuelta o le mostramos error
    header("Location: /php/dashboard.php?error=acceso_denegado");
    exit;
}

// 2. Verificar que los datos vengan por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger los datos del formulario
    $nombre = $_POST['nombre'];
    $serie = $_POST['serie'];
    $fecha = $_POST['fecha'];
    $unidades = $_POST['unidades'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];
    
    // 3. Procesar la imagen
    $nombre_imagen_final = "default.png"; // Imagen por defecto por si falla
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        
        $archivo_tmp = $_FILES['imagen']['tmp_name'];
        $nombre_archivo = $_FILES['imagen']['name'];
        
        // Obtener la extensión (ej. jpg, png, webp)
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        
        // Crear un nombre único para evitar sobreescribir imágenes con el mismo nombre
        $nombre_imagen_final = uniqid('img_') . '.' . $extension;
        
        // Ruta donde se guardará (tu carpeta de imágenes)
        $ruta_destino = public_path('img/' . $nombre_imagen_final);
        
        // Mover el archivo temporal a la carpeta final
        if (!move_uploaded_file($archivo_tmp, $ruta_destino)) {
            throw new RuntimeException((string) ("Error al subir la imagen. Verifica los permisos de la carpeta img."));
        }
    }

    // 4. Guardar en la base de datos usando PDO (Prepared Statements para seguridad)
    try {
        $sql = "INSERT INTO productos (nombre, serie, fecha, unidades, precio, imagen, categoria) 
                VALUES (:nombre, :serie, :fecha, :unidades, :precio, :imagen, :categoria)";
        
        $stmt = $conn->prepare($sql);
        
        // Vincular los parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':unidades', $unidades);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':imagen', $nombre_imagen_final); // Guardamos solo el nombre de la imagen
        $stmt->bindParam(':categoria', $categoria);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Si todo salió bien, redirigir al dashboard con un mensaje de éxito
header("Location: /php/dashboard.php?mensaje=guardado_exito");
             exit;
        } else {
            echo "Error al guardar en la base de datos.";
        }
        
    } catch (PDOException $e) {
        // Mostrar error si la consulta falla (muy útil para depurar)
        throw new RuntimeException((string) ("Error de base de datos: " . $e->getMessage()));
    }

} else {
    // Si alguien intenta entrar directo a la URL sin mandar datos
    header("Location: /php/dashboard.php");
    exit;
}
?>


