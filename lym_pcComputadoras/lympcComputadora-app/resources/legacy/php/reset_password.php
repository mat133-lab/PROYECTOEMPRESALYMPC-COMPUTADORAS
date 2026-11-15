<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = false;
$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$tokenIsValid = false;
$tokenHash = '';

if (preg_match('/^[a-f0-9]{64}$/i', $token)) {
    try {
        $tokenHash = hash('sha256', $token);
        $stmt = $conn->prepare('SELECT id_usuario FROM usuarios WHERE reset_token IN (?, ?) AND reset_expiration > NOW() LIMIT 1');
        $stmt->execute([$tokenHash, $token]);
        $tokenIsValid = (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        error_log('No se pudo validar el token de recuperación.');
        $error = 'No se pudo validar el enlace ahora. Inténtalo más tarde.';
    }
} else {
    $error = 'El enlace de recuperación no es válido o expiró. Solicita uno nuevo.';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && $tokenIsValid) {
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $stmt = $conn->prepare('UPDATE usuarios SET `contraseña` = ?, reset_token = NULL, reset_expiration = NULL WHERE reset_token IN (?, ?) AND reset_expiration > NOW()');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $tokenHash, $token]);
            $success = $stmt->rowCount() === 1;
            if (! $success) {
                $tokenIsValid = false;
                $error = 'El enlace ya fue utilizado o expiró. Solicita uno nuevo.';
            }
        } catch (Throwable $exception) {
            error_log('No se pudo actualizar la contraseña desde recuperación.');
            $error = 'No se pudo actualizar la contraseña ahora. Inténtalo más tarde.';
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
    <link rel="stylesheet" href="<?= legacy_asset_url('css/auth.css') ?>">
    <title>Restablecer contraseña - L&amp;M PC Computadoras</title>
</head>
<body>
    <main class="login-background">
        <section class="login-container" style="max-width:520px;">
            <header class="login-header">
                <i class="fas fa-user-lock" style="font-size:3rem;color:#ff7700;"></i>
                <h1>Nueva contraseña</h1>
                <p>Elige una contraseña de al menos 8 caracteres.</p>
            </header>
            <?php if ($error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" role="status">Tu contraseña se actualizó correctamente. <a href="../php/login.php">Inicia sesión</a>.</div>
            <?php elseif ($tokenIsValid): ?>
                <form class="login-form" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-group"><label for="password">Nueva contraseña</label><div class="input-container"><i class="fas fa-lock input-icon"></i><input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required></div></div>
                    <div class="input-group"><label for="confirm_password">Confirmar contraseña</label><div class="input-container"><i class="fas fa-lock input-icon"></i><input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required></div></div>
                    <button type="submit" class="login-btn"><span class="btn-text">Guardar contraseña</span></button>
                </form>
            <?php else: ?>
                <p style="text-align:center"><a href="../php/contraseña.php">Solicitar otro enlace</a></p>
            <?php endif; ?>
            <p class="register-footer" style="text-align:center"><a href="../php/login.php">Volver al inicio de sesión</a></p>
        </section>
    </main>
</body>
</html>
