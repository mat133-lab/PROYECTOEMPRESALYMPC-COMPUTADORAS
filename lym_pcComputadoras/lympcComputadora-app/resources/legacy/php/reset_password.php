<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';
$token_valido = false;
$email_usuario = '';

// 1. Verificar si viene un token en la URL
if(isset($_GET['token'])){
    $token = $_GET['token'];
    
    // Buscar si el token existe y si NO ha expirado
    $stmt = $conn->prepare("SELECT correo FROM usuarios WHERE reset_token = ? AND reset_expiration > NOW()");
    $stmt->execute([$token]);
    
    if($stmt->rowCount() > 0){
        $token_valido = true;
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email_usuario = $row['correo'];
    } else {
        $error = "El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.";
    }
} else {
    $error = "No se proporcionó ningún token de seguridad.";
}

// 2. Procesar el formulario cuando se envía la nueva contraseña
if($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido){
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($password) || empty($confirm_password)){
        $error = "Por favor, llena todos los campos.";
    } elseif($password !== $confirm_password){
        $error = "Las contraseñas no coinciden.";
    } elseif(strlen($password) < 6){
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        try {
            // Encriptar la nueva contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña en la base de datos y limpiar los tokens
            $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ?, reset_token = NULL, reset_expiration = NULL WHERE correo = ?");
            $stmt->execute([$hashed_password, $email_usuario]);
            
            $success = "¡Tu contraseña ha sido actualizada con éxito!";
            $token_valido = false; // Ocultamos el formulario para que inicie sesión
        } catch(PDOException $e) {
            $error = "Error al actualizar la contraseña: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/img/logo.webp">
    <link rel="stylesheet" href="/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <title>Restablecer Contraseña - L&M PC Computadoras</title>
</head>
<body>
    <div class="login-background">
        <div class="login-container">

            <div class="login-header">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-user-lock"></i>
                </div>
                <h2>Nueva Contraseña</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">
                    Ingresa tu nueva contraseña para acceder al sistema.
                </p>
            </div>

            <?php if($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="/php/login.php" style="color: #155724; font-weight: bold; text-decoration: underline;">Haz clic aquí para iniciar sesión</a>
            </div>
            <?php endif; ?>

            <?php if($token_valido && !$success): ?>
            <form id="resetForm" class="login-form" method="POST">
                <div class="input-group">
                    <label for="password">Nueva Contraseña:</label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required>
                    </div>
                </div>

                <div class="register-footer">
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Guardar Contraseña</span>
                        <i class="fas fa-save btn-icon"></i>
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <?php if(!$token_valido && !$success): ?>
            <div class="register-footer" style="margin-top: 20px; text-align: center;">
                <a href="/php/contraseña.php" style="color:var(--primary-color); text-decoration:none; font-weight: bold;">
                    <i class="fas fa-arrow-left"></i> Volver a intentar
                </a>
            </div>
            <?php endif; ?>

            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>
        </div>
    </div>
</body>
</html>


