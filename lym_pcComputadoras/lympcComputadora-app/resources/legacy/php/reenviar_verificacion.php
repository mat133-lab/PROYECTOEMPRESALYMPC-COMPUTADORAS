<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/email_verification.php';

$sentRequest = false;
$error = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe una dirección de correo válida.';
    } else {
        try {
            $stmt = $conn->prepare('SELECT usuario FROM usuarios WHERE correo = ? AND COALESCE(email_verified, 0) = 0 LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                sendEmailVerification($conn, $email, (string) $user['usuario']);
            }
        } catch (Throwable $exception) {
            error_log('No se pudo procesar la solicitud de reenvío de verificación.');
        }

        // Keep this response generic so it does not reveal account state.
        $sentRequest = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reenviar verificación - L&amp;M PC Computadoras</title>
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="<?= legacy_asset_url('css/auth.css') ?>">
</head>
<body>
    <main class="login-background">
        <section class="login-container" style="max-width:520px;">
            <header class="login-header">
                <i class="fas fa-envelope-circle-check" style="font-size:3rem;color:#ff7700;"></i>
                <h1>Verifica tu correo</h1>
                <p>Te enviaremos un nuevo enlace si la cuenta está pendiente de verificación.</p>
            </header>

            <?php if ($sentRequest): ?>
                <div class="alert alert-success" role="status">
                    Si la cuenta existe y aún no está verificada, recibirás un correo con un enlace válido durante 24 horas. Revisa también la carpeta de spam.
                </div>
            <?php elseif ($error !== ''): ?>
                <div class="alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (! $sentRequest): ?>
            <form method="POST" class="login-form">
                <div class="input-group">
                    <label for="email">Correo de registro</label>
                    <div class="input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required>
                    </div>
                </div>
                <button type="submit" class="login-btn"><span class="btn-text">Enviar enlace</span></button>
            </form>
            <?php endif; ?>

            <p class="register-footer" style="text-align:center;margin-top:20px;">
                <a href="../php/login.php">Volver al inicio de sesión</a>
            </p>
        </section>
    </main>
</body>
</html>
