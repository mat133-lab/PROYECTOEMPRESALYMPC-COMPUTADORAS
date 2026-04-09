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
    $cedula = trim($_POST['cedula']);

    // Validaciones
    if(empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role) || empty($cedula)){
        $error = "Todos los campos obligatorios deben estar llenos.";
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
    elseif(strlen($cedula) < 10){
        $error = "El número de cédula debe tener al menos 10 caracteres";
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

            $ruta_ruc = null;
            $ruta_cedula = null;
            $directorio_subida = "../uploads/";

            // Subir RUC
            if (isset($_FILES['archivo_ruc']) && $_FILES['archivo_ruc']['error'] === UPLOAD_ERR_OK){
                $destino_ruc = $directorio_subida . time() . "_ruc_" . basename($_FILES['archivo_ruc']['name']);
                if (move_uploaded_file($_FILES['archivo_ruc']['tmp_name'], $destino_ruc)) {
                    $ruta_ruc = $destino_ruc;
                }
            }

            // Subir Cédula
            if (isset($_FILES['archivo_cedula']) && $_FILES['archivo_cedula']['error'] === UPLOAD_ERR_OK){
                $destino_cedula = $directorio_subida . time() . "_cedula_" . basename($_FILES['archivo_cedula']['name']);
                if (move_uploaded_file($_FILES['archivo_cedula']['tmp_name'], $destino_cedula)) {
                    $ruta_cedula = $destino_cedula;
                }
            }

            // Insertar nuevo usuario (Ahora con los campos de archivos)
            try{
                $stmt = $conn->prepare("INSERT INTO usuarios (usuario, correo, contraseña, rol, archivo_ruc, archivo_cedula, cedula) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $role, $ruta_ruc, $ruta_cedula, $cedula]);
                
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
                <i class="fa-solid fa-id-card"
                    style="font-size: 4rem; color: var(--text-color, #ff7700); margin-bottom: 1rem;"></i>
                <h2>Crear Cuenta</h2>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Registrate para crear tu perfil</p>
            </div>

            <?php if($error): ?>
            <div class="alert alert-error"
                style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; border: 1px solid #f5c6cb;">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="alert alert-success"
                style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb;">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form id="registerForm" class="login-form" method="POST" enctype="multipart/form-data">

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

                <div class="input-group"
                    style="background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px dashed #ccc;">
                    <label
                        style="color: var(--text-color, #ff7700); font-weight: bold; margin-bottom: 10px; display: block;">Documentos</label>

                    <label for="archivo_ruc" style="font-size: 0.85rem;">Subir RUC (Si eres empresa):</label>
                    <div class="input-container" style="margin-bottom: 10px;">
                        <i class="fas fa-file-pdf input-icon"></i>
                        <input type="file" id="archivo_ruc" name="archivo_ruc" accept=".pdf, .jpg, .png"
                            style="padding-left: 40px; font-size: 0.85rem;">
                    </div>

                    <label for="archivo_cedula" style="font-size: 0.85rem;">Subir Copia de Cédula:</label>
                    <div class="input-container">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="file" id="archivo_cedula" name="archivo_cedula" accept=".pdf, .jpg, .png"
                            style="padding-left: 40px; font-size: 0.85rem;">
                    </div>
                </div>

                <div class="input-group">
                    <label for="contrasena">Ingresa tu Numero de Cedula:</label>
                    <div class="input-container">
                        <i class="fa-solid fa-address-card"></i>
                        <input type="password" id="cedula" name="cedula" placeholder="1234567890" required
                            autocomplete="current-password">
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
                        <input type="password" id="regConfirm" name="confirmPassword" placeholder="Repetir contraseña"
                            required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="regRole">Tipo de Usuario</label>
                    <div class="input-container">
                        <i class="fas fa-users-cog input-icon"></i>
                        <select id="regRole" name="role" class="tech-select" required
                            style="width: 100%; padding: 10px 10px 10px 40px; border: 1px solid #ccc; border-radius: 5px; outline: none;">
                            <option value="" disabled selected>Seleccione un rol...</option>
                            <option value="usuario">Usuario Común</option>
                            <option value="tecnico">Técnico</option>
                            <option value="encargado">Encargado</option>
                            <option value="asistente">Asistente</option>
                            <option value="pasante">Pasante</option>
                        </select>
                    </div>
                </div>

                <div class="register-footer" style="margin-top: 20px;">
                    <button type="submit" class="login-btn">
                        <span class="btn-text">Registrarse</span>
                        <i class="fas fa-user-plus btn-icon"></i>
                    </button>
                </div>

                <div style="margin-top:12px; text-align:center;">
                    <a href="../php/login.php" id="showLogin"
                        style="font-size:14px; color:#2b6cb0; text-decoration:none; display:flex; justify-content: center; gap:8px; align-items:center;">
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