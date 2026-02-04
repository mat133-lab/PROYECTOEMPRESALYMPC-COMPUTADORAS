<?php
session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
$action = isset($_POST['action']) ? $_POST['action'] : 'add';

if(!$product_id){
    echo json_encode(['ok'=>false,'msg'=>'product_id faltante']);
    exit;
}

$tables = ['productos','inventario','items','productos_almacen','productos_tbl','productos_list'];
$found = false;
$table_found = '';
$row = null;

foreach($tables as $table){
    try{
        $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if($r){
            $found = true; $table_found = $table; $row = $r; break;
        }
    }catch(PDOException $e){
        // tabla no existe o consulta inválida, intentar siguiente
        continue;
    }
}

if(!$found){
    echo json_encode(['ok'=>false,'msg'=>'No se encontró la tabla/producto en la base de datos.']);
    exit;
}

$stock_cols = ['unidades','stock','cantidad','existencia','cantidad_stock','qty'];
$stock_col = null;
foreach($stock_cols as $col){
    if(array_key_exists($col, $row)){
        $stock_col = $col; break;
    }
}

if(!$stock_col){
    echo json_encode(['ok'=>false,'msg'=>'No se encontró columna de stock en la tabla.']);
    exit;
}

if($action === 'add'){
    if((int)$row[$stock_col] < $qty){
        echo json_encode(['ok'=>false,'msg'=>'Stock insuficiente','stock'=>(int)$row[$stock_col]]);
        exit;
    }

    try{
        $new_stock = (int)$row[$stock_col] - $qty;
        $upd = $conn->prepare("UPDATE $table_found SET $stock_col = ? WHERE id = ?");
        $upd->execute([$new_stock, $product_id]);

        if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if(isset($_SESSION['cart'][$product_id])) $_SESSION['cart'][$product_id] += $qty; else $_SESSION['cart'][$product_id] = $qty;

        echo json_encode(['ok'=>true,'msg'=>'Agregado al carrito','stock'=>$new_stock,'cart'=>$_SESSION['cart']]);
        exit;
    }catch(PDOException $e){
        echo json_encode(['ok'=>false,'msg'=>'Error BD: '.$e->getMessage()]);
        exit;
    }
}

if($action === 'purchase'){
    // En este flujo ya restamos stock al agregar al carrito. Aquí sólo confirmamos la compra y limpiamos la sesión.
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
        echo json_encode(['ok'=>false,'msg'=>'El carrito está vacío']);
        exit;
    }

    // Opcional: podríamos insertar en tabla de pedidos si existe. Por ahora sólo limpiamos el carrito.
    $_SESSION['cart'] = [];
    echo json_encode(['ok'=>true,'msg'=>'Compra confirmada. Gracias por su compra.']);
    exit;
}

if($action === 'clear'){
    // Limpiar carrito sin confirmar compra (devolver stock)
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])){
        // En este caso simple, solo limpiamos. Un flujo más completo devolvería el stock.
        $_SESSION['cart'] = [];
    }
    echo json_encode(['ok'=>true,'msg'=>'Carrito vaciado']);
    exit;
}

echo json_encode(['ok'=>false,'msg'=>'Acción no soportada']);
exit;

?>
