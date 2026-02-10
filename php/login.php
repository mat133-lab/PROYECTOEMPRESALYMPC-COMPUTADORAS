<?php 
session_start();
require_once '../includes/db.php';

// Si el usuario ya tiene sesión iniciada, redirigir al dashboard
if(isset($_SESSION['usuario'])){
    header("Location: ../php/dashboard.php");
    exit;
}

if($_SERVER['REQUEST_METHOD']=== 'POST'){
    $user = trim($_POST['correo']); // Limpiar espacios en blanco
    $pass = $_POST['contrasena'];

//consulta segura de las sentencias
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
$stmt->execute([$user]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar credenciales con hash seguro
if($row && password_verify($pass, $row['contraseña'])){
    $_SESSION['correo'] = $row['correo'];
    $_SESSION['id'] = $row['id'];
    // Guardar nombre de usuario si existe en la tabla
    // Asegurar que la variable de sesión se cree incluso si el campo usuario está vacío
    $_SESSION['usuario'] = !empty($row['usuario']) ? $row['usuario'] : 'Usuario';
    $_SESSION['rol'] = $row['rol'];
    header("Location: ../php/dashboard.php");
    exit;
}else{
    $error = "Credenciales Inválidas";
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
    <title>Login - L&M PC Computadoras</title>
</head>

<body>
    <div class="login-background">
        <div class="login-container">
            <div class="login-header">
                <i class="fa-regular fa-circle-user"
                    style="font-size: 4rem; color: #ff6200; margin-bottom: 1rem;"></i>
                <h2>Iniciar Sesion</h2>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Inicia sesion en tu perfil</p>
            </div>

            <?php if(isset($error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" class="login-form" method="POST">
                <div class="input-group">
                    <label for="correo">Ingresa tu Correo Electronico: </label>
                    <div class="input-container">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="contrasena">Ingresa tu Contraseña: </label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="contrasena" name="contrasena" placeholder="*****" required>
                    </div>
                </div>

                <div class="form-options">
                    <a href="../php/contraseña.php" class="forgot-password" style="font-size:14px; color:#2b6cb0; text-decoration:none; display:inline-flex; gap:8px; align-items:center;">Olvido su Contraseña?</a>
                </div>

                <button type="submit" class="login-btn">
                    <span class="btn-text">Ingresar</span>
                    <i class="fas fa-arrow-right btn-icon"></i>
                </button>

                <div style="margin-top:12px; text-align:center;">
                    <a href="../php/registro.php" id="showRegister"
                        style="font-size:14px; color:#2b6cb0; text-decoration:none; display:block; gap:8px; align-items:center;">
                        <span>¿No tienes cuenta? Haz click aqui</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="../php/login_admin.php" id="showRegister"
                        style="font-size:14px; color:#2b6cb0; text-decoration:none; display:inline-flex; gap:8px; align-items:center;">
                        <span>¿Eres Administrador? Haz click aqui</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </form>


            <div id="loginMessage" class="message hidden"></div>

            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>
        </div>
    </div>

</body>

</html>