<?php
session_start();
require_once '../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/mail_config.php';

if (!isset($_SESSION['rol']) || !in_array(strtolower($_SESSION['rol']), ['asistente', 'admin'], true)) {
    header('Location: ../php/login.php');
    exit();
}

$conn->exec("CREATE TABLE IF NOT EXISTS notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_remitente INT NULL,
    id_usuario INT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    correo_destino VARCHAR(255) NOT NULL,
    estado_correo VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$usuariosStmt = $conn->query("SELECT id_usuario, usuario, correo, rol FROM usuarios ORDER BY usuario ASC");
$usuarios = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);

$alertType = '';
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $destino = $_POST['destino'] ?? 'todos';
    $seleccionados = $_POST['usuarios'] ?? [];

    if ($titulo === '' || $mensaje === '') {
        $alertType = 'danger';
        $alertMessage = 'Completa el titulo y el mensaje de la notificacion.';
    } else {
        $destinatarios = $usuarios;
        if ($destino === 'seleccionados') {
            $ids = array_map('intval', $seleccionados);
            $destinatarios = array_values(array_filter($usuarios, fn($u) => in_array((int)$u['id_usuario'], $ids, true)));
        }

        $guardados = 0;
        $enviados = 0;
        $erroresCorreos = [];
        $stmt = $conn->prepare("INSERT INTO notificaciones (id_remitente, id_usuario, titulo, mensaje, correo_destino, estado_correo) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($destinatarios as $usuario) {
            if (empty($usuario['correo'])) {
                continue;
            }

            $body = $mensaje . "\n\nL&M PC Computadoras";
            $mailOk = false;

            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = SMTP_MAILER_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_MAILER_USERNAME;
                $mail->Password = SMTP_MAILER_PASSWORD;
                $mail->SMTPSecure = SMTP_MAILER_SECURE;
                $mail->Port = SMTP_MAILER_PORT;
                $mail->CharSet = SMTP_MAILER_CHARSET;

                $mail->setFrom(SMTP_MAILER_FROM, SMTP_MAILER_FROM_NAME);
                $mail->addAddress($usuario['correo'], $usuario['usuario']);

                $mail->Subject = $titulo;
                $mail->Body = $body;
                $mail->AltBody = strip_tags($body);
                $mail->isHTML(SMTP_MAILER_IS_HTML);

                $mail->send();
                $mailOk = true;
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                $erroresCorreos[] = $usuario['correo'] . ': ' . $e->getMessage();
            }

            $stmt->execute([
                $_SESSION['id_usuario'] ?? null,
                $usuario['id_usuario'],
                $titulo,
                $mensaje,
                $usuario['correo'],
                $mailOk ? 'enviado' : 'registrado'
            ]);

            $guardados++;
            if ($mailOk) {
                $enviados++;
            }
        }

        if (!empty($erroresCorreos)) {
            $alertType = 'warning';
            $alertMessage = "Notificacion registrada para {$guardados} usuario(s). Correos enviados por el servidor: {$enviados}. Errores: " . implode(' | ', $erroresCorreos);
        } else {
            $alertType = 'success';
            $alertMessage = "Notificacion registrada para {$guardados} usuario(s). Correos enviados por el servidor: {$enviados}.";
        }

        $alertType = 'success';
        $alertMessage = "Notificacion registrada para {$guardados} usuario(s). Correos enviados por el servidor: {$enviados}.";
    }
}

$historialStmt = $conn->query("SELECT * FROM notificaciones ORDER BY fecha_creacion DESC LIMIT 20");
$historial = $historialStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Crear Notificaciones - L&M PC Computadoras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard_pasante.css">
    <link rel="stylesheet" href="../css/asistente_tools.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <nav class="navbar navbar-dark bg-warning fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand position-absolute top-50 start-50 translate-middle m-0 text-truncate"
                href="../php/dashboard_asistente.php" style="max-width: 45%; text-align: center;">L&M PC Computadoras</a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="offcanvas offcanvas-end text-bg-warning" tabindex="-1" id="offcanvasDarkNavbar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <ul class="navbar-nav flex-grow-1 pe-3">
                        <li class="nav-item"><a class="nav-link" href="../php/dashboard_asistente.php">Panel de Asistente</a></li>
                        <li class="nav-item"><a class="nav-link" href="../php/lecturadoc.php">Documentos de Pasante</a></li>
                        <li class="nav-item"><a class="nav-link" href="../php/chat.php">Chat con Clientes</a></li>
                    </ul>
                    <div class="d-flex align-items-center mt-3">
                        <span class="text-white me-3">Buen dia, <b><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Asistente'); ?></b></span>
                        <a href="../php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container pasante-hero main-content">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Crear Notificaciones</h1>
                <p>Envia avisos a usuarios registrados y guarda el historial de cada notificacion.</p>
                <span class="summary-badge"><i class="fas fa-envelope"></i> Usuarios: <?php echo count($usuarios); ?></span>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <img src="../img/notifiUsu.webp" alt="Notificaciones" class="hero-image">
            </div>
        </div>

        <?php if ($alertType): ?>
        <div class="alert alert-<?php echo $alertType; ?> mt-4" role="alert"><?php echo htmlspecialchars($alertMessage); ?></div>
        <?php endif; ?>

        <section class="tool-card mt-4">
            <h2 class="tool-title h4">Nueva notificacion</h2>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="titulo">Titulo</label>
                    <input class="form-control" type="text" id="titulo" name="titulo" maxlength="180" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="destino">Destinatarios</label>
                    <select class="form-select" id="destino" name="destino">
                        <option value="todos">Todos los usuarios registrados</option>
                        <option value="seleccionados">Usuarios seleccionados</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" for="mensaje">Mensaje</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Seleccionar usuarios</label>
                    <div class="row g-2">
                        <?php foreach ($usuarios as $usuario): ?>
                        <div class="col-md-4">
                            <label class="form-check border rounded p-2 h-100">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="usuarios[]"
                                    value="<?php echo (int)$usuario['id_usuario']; ?>">
                                <span><?php echo htmlspecialchars($usuario['usuario']); ?></span>
                                <small class="d-block tool-muted"><?php echo htmlspecialchars($usuario['correo']); ?></small>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button class="btn tool-action px-4" type="submit"><i class="fas fa-paper-plane"></i> Enviar notificacion</button>
                </div>
            </form>
        </section>

        <section class="table-responsive tool-table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                    <tr><td colspan="4" class="text-center py-4">No hay notificaciones registradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($historial as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($item['correo_destino']); ?></td>
                        <td><span class="status-pill"><?php echo htmlspecialchars($item['estado_correo']); ?></span></td>
                        <td><?php echo htmlspecialchars($item['fecha_creacion']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer class="footer mt-5">
        <div class="footer-content container">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p class="footer-note">Panel del asistente para avisos y comunicacion con usuarios.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
