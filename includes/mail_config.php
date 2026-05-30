<?php
// Configuración SMTP para PHPMailer.
// Usa tu cuenta de Gmail real y una contraseña de aplicación de Google.
// No uses tu contraseña normal de Gmail aquí.

// 1) Activa la verificación en dos pasos en tu cuenta de Google.
// 2) Genera una contraseña de aplicación en https://myaccount.google.com/security.
// 3) Usa esa contraseña en SMTP_MAILER_PASSWORD.

const SMTP_MAILER_HOST = 'smtp.gmail.com';
const SMTP_MAILER_PORT = 587;
const SMTP_MAILER_USERNAME = 'mateo014esteban@gmail.com';
const SMTP_MAILER_PASSWORD = 'gmpatisvxvvpelam';
const SMTP_MAILER_FROM = 'mateo014esteban@gmail.com';
const SMTP_MAILER_FROM_NAME = 'L&M PC Computadoras';
const SMTP_MAILER_SECURE = 'tls';
const SMTP_MAILER_CHARSET = 'UTF-8';
const SMTP_MAILER_IS_HTML = false;
