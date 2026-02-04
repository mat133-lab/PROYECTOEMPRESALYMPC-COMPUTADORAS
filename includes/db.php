<?php
//configuracion de parametros de coneccion
$host = 'localhost';
$db_name = 'lympc_bd';
$username = 'root';
$password = ''; //Cambiar si tiene contraseña en HTML
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Configurar el modo de error para lanzar exepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die ("Conexión fallida: " . $e->getMessage());
    exit;
}

?>