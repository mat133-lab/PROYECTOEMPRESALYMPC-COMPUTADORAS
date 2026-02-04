<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role'];

    // Validaciones
    if(empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)){
        $error = "Todos los campos son obligatorios";
    }
    elseif(strlen($username) < 3){
        $error = "El nombre de usuario debe tener al menos 3 caracteres";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "El formato del correo electrónico no es válido";
    }
    elseif(strlen($password) < 6){
        $error = "La contraseña debe tener al menos 6 caracteres";
    }
    elseif($password !== $confirmPassword){
        $error = "Las contraseñas no coinciden";
    }
    else{
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$email]);
        
        if($stmt->rowCount() > 0){
            $error = "El correo electrónico ya está registrado";
        }
        else{
            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insertar nuevo usuario
            try{
                $stmt = $conn->prepare("INSERT INTO usuarios (usuario, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role]);
                
                $success = "Registro exitoso. Redirigiendo al login...";
                header("Refresh: 2; url=../php/login.php");
            }
            catch(PDOException $e){
                $error = "Error al registrar: " . $e->getMessage();
            }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Registro - L&M PC Computadoras</title>
</head>

<body>
    <div class="login-background">
        <div class="login-container">
            
            <div class="login-header">
                <i class="fa-solid fa-id-card" style="font-size: 4rem; color: var(--text-color, #ff7700); margin-bottom: 1rem;"></i>
                <h2>Crear Cuenta</h2>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Registrate para crear tu perfil</p>
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

            <form id="registerForm" class="login-form" method="POST">
                
            <div class="input-group">
                    <label for="regUsername">Nombre de Usuario: </label>
                    <div class="input-container">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="regUsername" name="username" placeholder="Nombre de usuario" required>
                    </div>
                </div> 
                <div class="input-group">
                    <label for="regEmail">Correo Electrónico: </label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="regEmail" name="email" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="regPassword">Contraseña: </label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="regPassword" name="password" placeholder="Crear contraseña" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="regConfirm">Confirmar Contraseña: </label>
                    <div class="input-container">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" id="regConfirm" name="confirmPassword" placeholder="Repetir contraseña" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="regRole">Tipo de Usuario</label>
                    <div class="input-container">
                        <i class="fas fa-users-cog input-icon"></i>
                        <select id="regRole" name="role" class="tech-select" required>
                            <option value="" disabled selected>Seleccione un rol...</option>
                            <option value="usuario">Usuario Común</option>
                            <option value="tecnico">Técnico</option>
                            <option value="encargado">Encargado</option>
                            <option value="asistente">Asistente</option>
                            <option value="pasante">Pasante</option>
                        </select>
                    </div>
                </div>

                <div class="register-footer">
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Registrarse</span>
                        <i class="fas fa-user-plus btn-icon"></i>
                    </button>
                </div>
                <div style="margin-top:12px; text-align:center;">
                    <a href="../php/login.php" id="showLogin"
                        style="font-size:14px; color:#2b6cb0; text-decoration:none; display:block; gap:8px; align-items:center;">
                        <span>¿Ya tienes Cuenta? Inicia Sesion Aqui</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </form>

            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>
        </div>
    </div>
    </body>
</html>