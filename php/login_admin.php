<?php
session_start();
require_once '../includes/db.php';

$mensaje = '';
$tipo_mensaje = '';

// PROCESAR LOGIN DE ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_admin'])) {
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $codigo_admin = $_POST['codigo_admin'];
    
    // Verificar código de administrador
    $codigo_correcto = 'ADMIN2026';
    
    if ($codigo_admin !== $codigo_correcto) {
        $mensaje = 'Código de administrador incorrecto';
        $tipo_mensaje = 'error';
    } elseif (empty($correo) || empty($contrasena)) {
        $mensaje = 'Por favor completa todos los campos';
        $tipo_mensaje = 'error';
    } else {
        // Verificar credenciales
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($contrasena, $usuario['contraseña'])) {
            // Verificar que tenga rol de admin
            if ($usuario['rol'] === 'admin') {
                $_SESSION['correo'] = $usuario['correo'];
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['usuario'] = $usuario['usuario'];
                $_SESSION['rol'] = 'admin';
                // Marcar sesión como admin iniciada (coincide con dashboardadmin.php)
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $usuario['usuario'];

                header("Location: dashboardadmin.php");
                exit();
            } else {
                $mensaje = 'Este usuario no tiene permisos de administrador';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Correo o contraseña incorrectos';
            $tipo_mensaje = 'error';
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
    <title>Iniciar Sesión Administrador - L&M PC Computadoras</title>
</head>

<body class="login-background">

    <div class="login-container">

        <div class="login-header">
            <i class="fas fa-shield-alt fa-2x"></i>
            <h2>Iniciar Sesión Admin</h2>
            <p>L&M PC Computadoras</p>
        </div>

        <?php if ($mensaje): ?>
        <div class="alert <?php echo $tipo_mensaje === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="login-form">

            <div class="input-group">
                <label for="correo">Correo Electrónico</label>
                <div class="input-container">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="correo" name="correo" placeholder="admin@ejemplo.com" required
                        autocomplete="email">
                </div>
            </div>

            <div class="input-group">
                <label for="contrasena">Contraseña</label>
                <div class="input-container">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required
                        autocomplete="current-password">
                </div>
            </div>

            <div class="input-group">
                <label for="codigo_admin">Código de Administrador</label>
                <div class="input-container">
                    <i class="fas fa-key input-icon"></i>
                    <input type="password" id="codigo_admin" name="codigo_admin" placeholder="Código secreto" required>
                </div>
            </div>

            <button type="submit" name="login_admin" class="login-btn" href="../php/dashboardadmin.php">
                Iniciar Sesión
                <i class="fas fa-sign-in-alt btn-icon"></i>
            </button>

        </form>
        <div style="margin-top:12px; text-align:center;">
            <a href="../php/registro_admin.php" id="showRegister"
                style="font-size:14px; color:#2b6cb0; text-decoration:none; display:inline-flex; gap:8px; align-items:center;">
                <span>¿No tienes Cuenta Administrador? Haz click aqui</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

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
    <script src="../js/admin.js"></script>
</body>

</html>