<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/mail_config.php';

function sendEmailVerification(PDO $conn, string $email, string $username): bool
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET email_verified = 0, email_verification_token = ?, email_verification_expires = ? WHERE correo = ?");
        $stmt->execute([$token, $expiresAt, $email]);

        if ($stmt->rowCount() === 0) {
            return false;
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/');
        $verifyLink = $protocol . $host . $basePath . '/verify_email.php?token=' . $token;

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_MAILER_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_MAILER_USERNAME;
        $mail->Password = SMTP_MAILER_PASSWORD;
        $mail->SMTPSecure = SMTP_MAILER_SECURE;
        $mail->Port = SMTP_MAILER_PORT;
        $mail->CharSet = SMTP_MAILER_CHARSET;
        $mail->isHTML(SMTP_MAILER_IS_HTML);

        $mail->setFrom(SMTP_MAILER_FROM, SMTP_MAILER_FROM_NAME);
        $mail->addAddress($email, $username);

        $mail->Subject = 'Verifica tu cuenta - L&M PC Computadoras';
        $mail->Body = "Hola $username,<br><br>Gracias por registrarte en L&M PC Computadoras.<br><br>Para activar tu cuenta y poder iniciar sesión, confirma tu correo haciendo clic en el siguiente enlace:<br><br><a href=\"$verifyLink\">Confirmar mi correo electrónico</a><br><br>Si no creaste esta cuenta, puedes ignorar este mensaje.<br><br>L&M PC Computadoras";
        $mail->AltBody = "Hola $username, gracias por registrarte en L&M PC Computadoras. Para activar tu cuenta, abre este enlace: $verifyLink";

        $mail->send();
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
