<?php

$composerAutoload = function_exists('base_path')
    ? base_path('vendor/autoload.php')
    : dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}
require_once __DIR__.'/mail_config.php';

function legacyAccountActionUrl(string $script, array $query = []): string
{
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? '/php/'.$script);
    if (function_exists('request') && function_exists('app')) {
        try {
            $requestUri = request()->getRequestUri();
            $origin = request()->getSchemeAndHttpHost();
        } catch (Throwable $exception) {
            $origin = null;
        }
    }

    if (! isset($origin)) {
        $https = (! empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        if (! preg_match('/^[A-Za-z0-9.\-\[\]:]+$/', $host)) {
            $host = 'localhost';
        }
        $origin = ($https ? 'https://' : 'http://').$host;
    }

    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/php/'.$script;
    $directory = rtrim(str_replace('\\', '/', dirname($path)), '/.');
    $url = rtrim($origin, '/').($directory === '' ? '' : $directory).'/'.$script;

    return $query === [] ? $url : $url.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

function legacyMailConfigured(): bool
{
    return defined('SMTP_MAILER_HOST') && trim((string) SMTP_MAILER_HOST) !== ''
        && defined('SMTP_MAILER_USERNAME') && trim((string) SMTP_MAILER_USERNAME) !== ''
        && defined('SMTP_MAILER_PASSWORD') && trim((string) SMTP_MAILER_PASSWORD) !== ''
        && defined('SMTP_MAILER_FROM') && filter_var(SMTP_MAILER_FROM, FILTER_VALIDATE_EMAIL) !== false;
}

function legacyMailRateLimit(string $purpose, string $email, int $maxPerHour = 3): bool
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $keys = [
        'account-mail:'.$purpose.':ip:'.hash('sha256', $ip),
        'account-mail:'.$purpose.':email:'.hash('sha256', strtolower(trim($email))),
    ];

    foreach ($keys as $key) {
        try {
            if (function_exists('app') && app()->bound('cache')) {
                $limiter = app(\Illuminate\Cache\RateLimiter::class);
                if ($limiter->tooManyAttempts($key, $key === $keys[0] ? 10 : $maxPerHour)) {
                    return false;
                }
                $limiter->hit($key, 3600);
                continue;
            }
        } catch (Throwable $exception) {
            // Direct PHP execution uses the native session limiter below.
        }

        $now = time();
        $_SESSION['account_mail_limits'][$key] = array_values(array_filter(
            $_SESSION['account_mail_limits'][$key] ?? [],
            static fn ($timestamp): bool => is_int($timestamp) && $timestamp > $now - 3600
        ));
        $limit = $key === $keys[0] ? 10 : $maxPerHour;
        if (count($_SESSION['account_mail_limits'][$key]) >= $limit) {
            return false;
        }
        $_SESSION['account_mail_limits'][$key][] = $now;
    }

    return true;
}

function legacySendAccountMail(string $recipient, string $name, string $subject, string $htmlBody, string $textBody): bool
{
    if (! legacyMailConfigured() || ! class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        error_log('No se pudo enviar correo de cuenta: SMTP o PHPMailer no está configurado.');
        return false;
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_MAILER_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_MAILER_USERNAME;
        $mail->Password = SMTP_MAILER_PASSWORD;
        $mail->SMTPSecure = SMTP_MAILER_SECURE;
        $mail->Port = SMTP_MAILER_PORT;
        $mail->Timeout = 15;
        $mail->CharSet = SMTP_MAILER_CHARSET;
        $mail->isHTML(true);
        $mail->setFrom(SMTP_MAILER_FROM, SMTP_MAILER_FROM_NAME);
        $mail->addAddress($recipient, $name);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;
        $mail->send();

        return true;
    } catch (Throwable $exception) {
        error_log('Fallo el envío SMTP de correo de cuenta.');
        return false;
    }
}

function sendEmailVerification(PDO $conn, string $email, string $username): bool
{
    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! legacyMailConfigured()
        || ! legacyMailRateLimit('verification', $email)) {
        return false;
    }

    try {
        $token = bin2hex(random_bytes(32));
        $storedToken = hash('sha256', $token);
        $stmt = $conn->prepare('UPDATE usuarios SET email_verified = 0, email_verification_token = ?, email_verification_expires = DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE correo = ? AND COALESCE(email_verified, 0) = 0');
        $stmt->execute([$storedToken, $email]);
        if ($stmt->rowCount() === 0) {
            return false;
        }

        $link = legacyAccountActionUrl('verify_email.php', ['token' => $token]);
        $safeName = htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = 'Hola '.$safeName.',<br><br>Gracias por registrarte en L&amp;M PC Computadoras. '
            .'Confirma tu correo en las próximas 24 horas para activar tu cuenta:<br><br>'
            .'<a href="'.$safeLink.'">Verificar correo</a><br><br>'
            .'Si no creaste esta cuenta, puedes ignorar este mensaje.';
        $text = "Hola {$username}, confirma tu correo en las próximas 24 horas: {$link}\n\nSi no creaste esta cuenta, ignora este mensaje.";

        return legacySendAccountMail($email, $username, 'Verifica tu cuenta - L&M PC Computadoras', $html, $text);
    } catch (Throwable $exception) {
        error_log('No se pudo preparar el correo de verificación.');
        return false;
    }
}

function sendPasswordResetEmail(PDO $conn, string $email): bool
{
    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! legacyMailConfigured()
        || ! legacyMailRateLimit('password-reset', $email)) {
        return false;
    }

    try {
        $stmt = $conn->prepare('SELECT id_usuario, usuario FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (! $user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $storedToken = hash('sha256', $token);
        $update = $conn->prepare('UPDATE usuarios SET reset_token = ?, reset_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id_usuario = ?');
        $update->execute([$storedToken, (int) $user['id_usuario']]);

        $link = legacyAccountActionUrl('reset_password.php', ['token' => $token]);
        $safeName = htmlspecialchars((string) $user['usuario'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = 'Hola '.$safeName.',<br><br>Recibimos una solicitud para restablecer tu contraseña. '
            .'El enlace vence en una hora:<br><br><a href="'.$safeLink.'">Restablecer contraseña</a>'
            .'<br><br>Si no solicitaste el cambio, ignora este mensaje.';
        $text = "Hola {$user['usuario']}, usa este enlace para restablecer tu contraseña (vence en una hora): {$link}\n\nSi no solicitaste el cambio, ignora este mensaje.";

        return legacySendAccountMail($email, (string) $user['usuario'], 'Recupera tu contraseña - L&M PC Computadoras', $html, $text);
    } catch (Throwable $exception) {
        error_log('No se pudo preparar el correo de recuperación.');
        return false;
    }
}
