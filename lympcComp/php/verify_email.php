<?php
session_start();
require_once '../includes/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($token !== '') {
    $stmt = $conn->prepare("SELECT id_usuario, correo, usuario FROM usuarios WHERE email_verification_token = ? AND email_verification_expires > NOW() AND email_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $conn->prepare("UPDATE usuarios SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL WHERE id_usuario = ?");
        $update->execute([$user['id_usuario']]);
        $success = 'Tu correo ha sido verificado correctamente. Ya puedes iniciar sesión.';
    } else {
        $error = 'El enlace de verificación no es válido o ha expirado.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de correo</title>
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <div class="login-background">
        <div class="login-container" style="max-width: 520px;">
            <div class="login-header">
                <i class="fas fa-envelope-circle-check" style="font-size: 3rem; color: #ff7700;"></i>
                <h2>Verificación de correo</h2>
                <p style="font-size: 0.9rem; color: var(--text-muted);">Confirma tu dirección de correo para activar tu cuenta.</p>
            </div>

            <?php if ($error): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; text-align: center; border: 1px solid #f5c6cb; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; text-align: center; border: 1px solid #c3e6cb; margin-bottom: 15px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div style="text-align:center; margin-top: 10px;">
                <a href="login.php" class="login-btn" style="display:inline-block; text-decoration:none;">Ir al login</a>
            </div>
        </div>
    </div>
</body>
</html>
