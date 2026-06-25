<?php
// Configuración SMTP para PHPMailer.
// Usa tu cuenta de Gmail real y una contraseña de aplicación de Google.
// No uses tu contraseña normal de Gmail aquí.

// 1) Activa la verificación en dos pasos en tu cuenta de Google.
// 2) Genera una contraseña de aplicación en https://myaccount.google.com/security.
// 3) Usa esa contraseña en SMTP_MAILER_PASSWORD.

define('SMTP_MAILER_HOST', getenv('SMTP_MAILER_HOST') ?: 'smtp.gmail.com');
define('SMTP_MAILER_PORT', (int) (getenv('SMTP_MAILER_PORT') ?: 587));
define('SMTP_MAILER_USERNAME', getenv('SMTP_MAILER_USERNAME') ?: 'mateo.cisneros@istvidanueva.edu.ec');
define('SMTP_MAILER_PASSWORD', getenv('SMTP_MAILER_PASSWORD') ?: 'iiwnnzzxcoybyiyq');
define('SMTP_MAILER_FROM', getenv('SMTP_MAILER_FROM') ?: (getenv('SMTP_MAILER_USERNAME') ?: 'mateo.cisneros@istvidanueva.edu.ec'));
define('SMTP_MAILER_FROM_NAME', getenv('SMTP_MAILER_FROM_NAME') ?: 'L&M PC Computadoras');
define('SMTP_MAILER_SECURE', getenv('SMTP_MAILER_SECURE') ?: 'tls');
define('SMTP_MAILER_CHARSET', getenv('SMTP_MAILER_CHARSET') ?: 'UTF-8');
define('SMTP_MAILER_IS_HTML', filter_var(getenv('SMTP_MAILER_IS_HTML') ?: '0', FILTER_VALIDATE_BOOL));
