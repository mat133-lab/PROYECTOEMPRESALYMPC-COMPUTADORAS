<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';
$token_valid = false;
$token = '';

// Verificar token en URL
if(isset($_GET['token'])){
    $token = $_GET['token'];
    
    try{
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expiration > NOW()");
        $stmt->execute([$token]);
        
        if($stmt->rowCount() > 0){
            $token_valid = true;
        }
        else{
            $error = "El token es inválido o ha expirado";
        }
    }
    catch(PDOException $e){
        // Si las columnas no existen, simplemente mostrar error
        $error = "Error al validar token. Por favor, solicita nuevamente la recuperación de contraseña.";
    }
}

// Procesar nueva contraseña
if($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid){
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($new_password) || empty($confirm_password)){
        $error = "Todos los campos son obligatorios";
    }
    elseif(strlen($new_password) < 6){
        $error = "La contraseña debe tener al menos 6 caracteres";
    }
    elseif($new_password !== $confirm_password){
        $error = "Las contraseñas no coinciden";
    }
    else{
        try{
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE usuarios SET contraseña = ?, reset_token = NULL, reset_expiration = NULL WHERE reset_token = ?");
            $stmt->execute([$hashed_password, $token]);
            
            $success = "Contraseña actualizada correctamente. Redirigiendo al login...";
            header("Refresh: 2; url=../php/login.php");
        }
        catch(PDOException $e){
            $error = "Error al actualizar contraseña: " . $e->getMessage();
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
    <title>Restablecer Contraseña - L&M PC Computadoras</title>
</head>

<body>
    <div class="login-background">
        <div class="login-container">

            <div class="login-header">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-lock-open"></i>
                </div>
                <h2>Restablecer Contraseña</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 8px;">
                    Ingresa tu nueva contraseña
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

            <?php if($token_valid): ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="newPassword">Nueva Contraseña: </label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="newPassword" name="new_password" placeholder="Nueva contraseña"
                            required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirmPassword">Confirmar Contraseña: </label>
                    <div class="input-container">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" id="confirmPassword" name="confirm_password"
                            placeholder="Repetir contraseña" required>
                    </div>
                </div>

                <div class="register-footer">
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Actualizar Contraseña</span>
                        <i class="fas fa-check btn-icon"></i>
                    </button>

                    <div class="register-footer">
                        <a href="../php/login.php" id="showRegister"
                            style="font-size:14px; color:#2b6cb0; text-decoration:none; display:inline-flex; gap:8px; align-items:center;">
                            <span>¿Volver al Login?</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </form>
            <?php else: ?>
            <div class="register-footer">
                <a href="../php/login.php" id="showRegister"
                    style="font-size:14px; color:#2b6cb0; text-decoration:none; display:inline-flex; gap:8px; align-items:center;">
                    <span>¿Volver al Login?</span>
                    <i class="fas fa-arrow-right"></i>
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