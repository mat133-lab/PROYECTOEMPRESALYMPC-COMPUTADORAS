<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/email_verification.php';

$error = '';
$success = false;
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ingresa una dirección de correo válida.';
    } else {
        // The same response is returned whether an account exists or SMTP is unavailable.
        sendPasswordResetEmail($conn, $email);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="<?= legacy_asset_url('css/auth.css') ?>">
    <title>Recuperar contraseña - L&amp;M PC Computadoras</title>
</head>
<body>
    <main class="login-background">
        <section class="login-container" style="max-width:520px;">
            <header class="login-header">
                <i class="fas fa-shield-alt" style="font-size:3rem;color:#ff7700;"></i>
                <h1>¿Olvidaste tu contraseña?</h1>
                <p>Introduce el correo de tu cuenta y te enviaremos un enlace de recuperación.</p>
            </header>
            <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="status">Si existe una cuenta con ese correo, recibirás un enlace para restablecer la contraseña. Revisa también la carpeta de spam.</div>
            <?php else: ?>
                <form class="login-form" method="POST">
                    <div class="input-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-container"><i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                        </div>
                    </div>
                    <button type="submit" class="login-btn"><span class="btn-text">Enviar correo</span><i class="fas fa-paper-plane btn-icon"></i></button>
                </form>
            <?php endif; ?>
            <p class="register-footer" style="text-align:center"><a href="../php/login.php">Volver al inicio de sesión</a></p>
            <div class="login-footer"><p>2026 - L&amp;M PC COMPUTADORAS ©</p></div>
        </section>
    </main>
</body>
</html>
