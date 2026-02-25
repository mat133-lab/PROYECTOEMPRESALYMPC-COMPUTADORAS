<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    
    // Validar que el campo no esté vacío
    if(empty($email)){
        $error = "Por favor, ingresa tu correo electrónico";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "El formato del correo electrónico no es válido";
    }
    else{
        // Verificar si el correo existe EXCLUSIVAMENTE en la tabla usuarios
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0){
            // El usuario SÍ existe, generamos el token
            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            try{
                // Guardar token en la base de datos
                $stmt = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_expiration = ? WHERE correo = ?");
                $stmt->execute([$token, $expiration, $email]);
                
                // Construir enlace de recuperación local
                $reset_link = "../php/reset_password.php?token=" . $token;
                
                // REDIRECCIONAR DIRECTAMENTE PARA PRUEBAS LOCALES
                header("Location: " . $reset_link);
                exit(); // Detenemos la ejecución aquí para que el salto sea inmediato
            }
            catch(PDOException $e){
                $error = "Error al procesar la solicitud: " . $e->getMessage();
            }
        }
        else{
            // EL CORREO NO EXISTE EN LA TABLA USUARIOS (Lanzamos error explícito)
            $error = "Este correo electrónico no está registrado como usuario en el sistema.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <title>Recuperar Contraseña - L&M PC Computadoras</title>
</head>

<body>
    <div class="login-background">
        <div class="login-container">

            <div class="login-header">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>¿Olvidaste tu contraseña?</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">
                    Introduce tu correo y te enviaremos instrucciones para restablecerla.
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
            </div>
            <?php endif; ?>

            <form id="forgotForm" class="login-form" method="POST">
                <div class="input-group">
                    <label for="recoveryEmail">Correo Electrónico registrado: </label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="recoveryEmail" name="email" placeholder="carlos@ejemplo.com" required>
                    </div>
                </div>

                <div class="register-footer">
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Enviar Instrucciones</span>
                        <i class="fas fa-paper-plane btn-icon"></i>
                    </button>

                    <div class="register-footer">
                        <a href="../php/login.php" id="showRegister"
                            style="font-size:14px; color:#2b6cb0; text-decoration:none; display:block; gap:8px; align-items:center;">
                            <span>¿Volver al Login?</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </form>

            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>
        </div>
    </div>
</body>

</html>