<?php
$host = 'localhost';
$db_name = 'lympc_bd';
$username = 'root';
$password = ''; 

try {
    $port = '3306';
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    
    try {
        $port = '3307';
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$db_name;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e2) {
        die("Conexión fallida en ambos puertos: " . $e2->getMessage());
    }
}
?>