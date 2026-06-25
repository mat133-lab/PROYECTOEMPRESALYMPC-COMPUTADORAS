<?php

define('SMTP_MAILER_HOST', env('SMTP_MAILER_HOST', env('MAIL_HOST', 'smtp.gmail.com')));
define('SMTP_MAILER_PORT', (int) env('SMTP_MAILER_PORT', env('MAIL_PORT', 587)));
define('SMTP_MAILER_USERNAME', env('SMTP_MAILER_USERNAME', env('MAIL_USERNAME', 'mateo.cisneros@istvidanueva.edu.ec')));
define('SMTP_MAILER_PASSWORD', env('SMTP_MAILER_PASSWORD', env('MAIL_PASSWORD', 'iiwnnzzxcoybyiyq')));
define('SMTP_MAILER_FROM', env('SMTP_MAILER_FROM', env('MAIL_FROM_ADDRESS', SMTP_MAILER_USERNAME)));
define('SMTP_MAILER_FROM_NAME', env('SMTP_MAILER_FROM_NAME', env('MAIL_FROM_NAME', 'L&M PC Computadoras')));
define('SMTP_MAILER_SECURE', env('SMTP_MAILER_SECURE', 'tls'));
define('SMTP_MAILER_CHARSET', env('SMTP_MAILER_CHARSET', 'UTF-8'));
define('SMTP_MAILER_IS_HTML', filter_var(env('SMTP_MAILER_IS_HTML', '0'), FILTER_VALIDATE_BOOLEAN));