<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if (!$product_id && $action === 'add') {
    echo json_encode(['ok'=>false, 'msg'=>'Falta el ID del producto']);
    return;
}

// AGREGAR AL CARRITO
if ($action === 'add') {
    try {
        // Buscamos el producto exactamente por id_producto
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['ok'=>false, 'msg'=>'El producto no existe en la base de datos.']);
            return;
        }

        // Verificamos si hay stock suficiente en la columna unidades en nuestra bd
        $stock_actual = (int)$row['unidades'];
        if ($stock_actual < $qty) {
            echo json_encode(['ok'=>false, 'msg'=>'Stock insuficiente', 'stock'=>$stock_actual]);
            return;
        }

        // Restamos las unidades y actualizamos la base de datos
        $new_stock = $stock_actual - $qty;
        $upd = $conn->prepare("UPDATE productos SET unidades = ? WHERE id_producto = ?");
        $upd->execute([$new_stock, $product_id]);

        // Guardamos en la variable de sesión 
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }

        echo json_encode([
            'ok' => true, 
            'msg' => 'Producto agregado al carrito', 
            'stock' => $new_stock,
            'producto' => [
                'id' => $row['id_producto'],
                'nombre' => $row['nombre'],
                'serie' => $row['serie'],
                'fecha' => $row['fecha'],
                'precio' => $row['precio'],
                'imagen' => $row['imagen'],
                'categoria' => $row['categoria'],
            ]
        ]);
        return;

    } catch (PDOException $e) {
        echo json_encode(['ok'=>false, 'msg'=>'Error SQL: ' . $e->getMessage()]);
        return;
    } catch (Exception $e) {
        echo json_encode(['ok'=>false, 'msg'=>'Error PHP: ' . $e->getMessage()]);
        return;
    }
}


// Accion para la Compra 
if ($action === 'purchase') {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['ok'=>false, 'msg'=>'El carrito está vacío']);
        return;
    }

    // Aquí simplemente vaciamos el carrito simulando la compra exitosa
    $_SESSION['cart'] = [];
    echo json_encode(['ok'=>true, 'msg'=>'¡Compra confirmada! Gracias por su compra.']);
    return;
}

// VACIAR CARRITO
if ($action === 'clear') {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    echo json_encode(['ok'=>true, 'msg'=>'Carrito vaciado']);
    return;
}

// acciones no validas o no soportadas
echo json_encode(['ok'=>false, 'msg'=>'Acción no soportada']);
return;
?>


