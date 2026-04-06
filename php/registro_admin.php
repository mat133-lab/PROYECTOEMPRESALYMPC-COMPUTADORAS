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

            // Registrar nuevo admin (con campos de archivos)
            $contraseña_hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, correo, contraseña, rol, archivo_ruc, archivo_cedula) VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$usuario, $correo, $contraseña_hash, 'admin', $ruta_ruc, $ruta_cedula])) {
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
            <div class="alert <?php echo $tipo_mensaje === 'success' ? 'alert-success' : 'alert-error'; ?>" style="padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; border: 1px solid <?php echo $tipo_mensaje === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>; background-color: <?php echo $tipo_mensaje === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $tipo_mensaje === 'success' ? '#155724' : '#721c24'; ?>;">
                <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="login-form" id="form-registro" enctype="multipart/form-data">

                <div class="input-group">
                    <label>Nombre de Usuario</label>
                    <div class="input-container">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="usuario" name="usuario" required value="<?php echo $_POST['usuario'] ?? ''; ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="correo" name="correo" required value="<?php echo $_POST['correo'] ?? ''; ?>">
                    </div>
                </div>

                <div class="input-group" style="background: rgba(0,0,0,0.03); padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px dashed #ccc;">
                    <label style="color: var(--text-color, #ff7700); font-weight: bold; margin-bottom: 10px; display: block;">Documentos</label>
                    
                    <label for="archivo_ruc" style="font-size: 0.85rem;">Subir RUC:</label>
                    <div class="input-container" style="margin-bottom: 10px;">
                        <i class="fas fa-file-pdf input-icon"></i>
                        <input type="file" id="archivo_ruc" name="archivo_ruc" accept=".pdf, .jpg, .png" style="padding-left: 40px; font-size: 0.85rem;">
                    </div>

                    <label for="archivo_cedula" style="font-size: 0.85rem;">Subir Copia de Cédula:</label>
                    <div class="input-container">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="file" id="archivo_cedula" name="archivo_cedula" accept=".pdf, .jpg, .png" style="padding-left: 40px; font-size: 0.85rem;">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Confirmar Contraseña</label>
                    <div class="input-container">
                        <i class="fas fa-check-circle input-icon"></i>
                        <input type="password" id="confirm_contrasena" name="confirm_contrasena" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Código de Administrador</label>
                    <div class="input-container">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" id="codigo_admin" name="codigo_admin" required>
                    </div>
                </div>

                <button type="submit" name="registro_admin" class="login-btn">
                    Registrarse
                    <i class="fas fa-user-plus btn-icon"></i>
                </button>

            </form>
            
            <div class="login-footer">
                <div class="login-link-container">
                    <a href="../php/login.php" style="color:#2b6cb0; text-decoration:none;">
                        <i class="fas fa-arrow-left"></i> Volver al inicio
                    </a>
                </div>
            </div>
            <div class="login-footer">
                <p>2026 - L&M PC COMPUTADORAS ©</p>
            </div>

        </div>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>