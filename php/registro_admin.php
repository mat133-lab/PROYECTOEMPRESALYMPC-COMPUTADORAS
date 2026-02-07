<?php
session_start();
require_once '../includes/db.php';

$mensaje = '';
$tipo_mensaje = '';

// PROCESAR REGISTRO DE ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro_admin'])) {
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $confirm_contrasena = $_POST['confirm_contrasena'];
    $codigo_admin = $_POST['codigo_admin'];
    
    // Verificar código de administrador
    $codigo_correcto = 'ADMIN2026';
    
    if ($codigo_admin !== $codigo_correcto) {
        $mensaje = 'Código de administrador incorrecto';
        $tipo_mensaje = 'error';
    } elseif (empty($usuario) || empty($correo) || empty($contrasena) || empty($confirm_contrasena)) {
        $mensaje = 'Por favor completa todos los campos';
        $tipo_mensaje = 'error';
    } elseif ($contrasena !== $confirm_contrasena) {
        $mensaje = 'Las contraseñas no coinciden';
        $tipo_mensaje = 'error';
    } elseif (strlen($contrasena) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'error';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El correo electrónico no es válido';
        $tipo_mensaje = 'error';
    } else {
        // Verificar si el correo ya existe
        $stmt_check = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt_check->execute([$correo]);
        
        if ($stmt_check->rowCount() > 0) {
            $mensaje = 'Este correo ya está registrado';
            $tipo_mensaje = 'error';
        } else {
            // Registrar nuevo admin
            $contraseña_hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, correo, contraseña, rol) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$usuario, $correo, $contraseña_hash, 'admin'])) {
                $mensaje = 'Administrador registrado exitosamente. Por favor, inicia sesión.';
                $tipo_mensaje = 'success';
                // Limpiar formulario
                $_POST = array();
            } else {
                $mensaje = 'Error al registrar el administrador';
                $tipo_mensaje = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/estiloadmin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Registrarse como Administrador - L&M PC Computadoras</title>
</head>

<body class="admin-page">
    <div class="login-background">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-shield fa-2x"></i>
                <h2>Registro Administrador</h2>
                <p>L&M PC Computadoras</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert <?php echo $tipo_mensaje === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <i
                    class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="login-form">

                <div class="input-group">
                    <label>Nombre de Usuario</label>
                    <div class="input-container">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="usuario" required value="<?php echo $_POST['usuario'] ?? ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="correo" required value="<?php echo $_POST['correo'] ?? ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="contrasena" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirmar Contraseña</label>
                    <div class="input-container">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" name="confirm_contrasena" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Código de Administrador</label>
                    <div class="input-container">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="codigo_admin" required>
                    </div>
                </div>

                <button type="submit" name="registro_admin" class="login-btn">
                    Registrarse
                    <i class="fas fa-user-plus btn-icon"></i>
                </button>

            </form>
            <div class="login-footer">
                <div class="login-link-container">
                    <a href="../php/login.php">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                </div>
            </div>
            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>

        </div>
    </div>

</body>


</html>