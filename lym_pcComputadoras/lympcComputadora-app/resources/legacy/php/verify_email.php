<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';
$token = (string) ($_GET['token'] ?? '');

if (preg_match('/^[a-f0-9]{64}$/i', $token)) {
    try {
        $hashedToken = hash('sha256', $token);
        $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE email_verification_token IN (?, ?) AND email_verification_expires > NOW() AND COALESCE(email_verified, 0) = 0 LIMIT 1');
        $stmt->execute([$hashedToken, $token]); // Also accepts links created before tokens were hashed.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $update = $conn->prepare('UPDATE usuarios SET email_verified = 1, email_verification_token = NULL, email_verification_expires = NULL WHERE id_usuario = ? AND email_verification_token IN (?, ?) AND email_verification_expires > NOW() AND COALESCE(email_verified, 0) = 0');
            $update->execute([(int) $user['id_usuario'], $hashedToken, $token]);
            $success = $update->rowCount() === 1
                ? 'Tu correo fue verificado. Ya puedes iniciar sesión.'
                : 'El enlace ya fue utilizado o expiró. Solicita uno nuevo.';
        } else {
            $error = 'El enlace no es válido, ya fue utilizado o expiró. Solicita uno nuevo.';
        }
    } catch (Throwable $exception) {
        error_log('No se pudo completar la verificación de correo.');
        $error = 'No se pudo verificar el correo ahora. Inténtalo más tarde.';
    }
} else {
    $error = 'El enlace de verificación no es válido. Solicita uno nuevo.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de correo</title>
    <link rel="shortcut icon" href="../img/logo.webp">
    <link rel="stylesheet" href="<?= legacy_asset_url('css/auth.css') ?>">
</head>
<body>
    <main class="login-background">
        <section class="login-container" style="max-width:520px;">
            <header class="login-header">
                <i class="fas fa-envelope-circle-check" style="font-size:3rem;color:#ff7700;"></i>
                <h1>Verificación de correo</h1>
                <p>Confirma tu correo para activar la cuenta.</p>
            </header>
            <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error): ?><p style="text-align:center"><a href="../php/reenviar_verificacion.php">Solicitar otro enlace</a></p><?php endif; ?>
            <p class="register-footer" style="text-align:center"><a href="../php/login.php">Ir al inicio de sesión</a></p>
        </section>
    </main>
</body>
</html>
