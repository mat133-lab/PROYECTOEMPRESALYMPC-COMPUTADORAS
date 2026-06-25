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

try {
    $checkColumns = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'email_verified'");
    if ($checkColumns->rowCount() === 0) {
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0");
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verification_token VARCHAR(255) DEFAULT NULL");
        $conn->exec("ALTER TABLE usuarios ADD COLUMN email_verification_expires DATETIME DEFAULT NULL");
    }

    $conn->exec("UPDATE usuarios SET email_verified = 1 WHERE email_verified IS NULL OR (email_verified = 0 AND email_verification_token IS NULL AND email_verification_expires IS NULL)");
} catch (PDOException $e) {
    error_log('Error al preparar verificación por correo: ' . $e->getMessage());
}
?>