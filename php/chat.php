<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: ../php/login.php');
    exit();
}

$rol = strtolower($_SESSION['rol'] ?? 'usuario');
$esAsistente = in_array($rol, ['asistente', 'admin', 'tecnico'], true);
$idUsuario = $_SESSION['id_usuario'] ?? null;
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';

$conn->exec("CREATE TABLE IF NOT EXISTS chat_conversaciones (
    id_conversacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    nombre_usuario VARCHAR(200) NOT NULL,
    correo_usuario VARCHAR(255) NULL,
    estado VARCHAR(40) NOT NULL DEFAULT 'ia',
    tema VARCHAR(180) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->exec("CREATE TABLE IF NOT EXISTS chat_mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_conversacion INT NOT NULL,
    remitente VARCHAR(40) NOT NULL,
    nombre_remitente VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (id_conversacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function respuestaIA($mensaje) {
    $texto = function_exists('mb_strtolower') ? mb_strtolower($mensaje, 'UTF-8') : strtolower($mensaje);
    $necesitaHumano = false;

    if (str_contains($texto, 'humano') || str_contains($texto, 'asistente') || str_contains($texto, 'personalizada') || str_contains($texto, 'especifica')) {
        $necesitaHumano = true;
        $respuesta = 'Te voy a pasar con asistencia para revisar tu caso con mas detalle. Un asistente continuara la conversacion aqui.';
    } elseif (str_contains($texto, 'horario') || str_contains($texto, 'disponible') || str_contains($texto, 'dias')) {
        $respuesta = 'Atendemos consultas sobre horarios y citas tecnicas. Puedes indicar el dia que prefieres y el tipo de equipo para revisar disponibilidad.';
    } elseif (str_contains($texto, 'direccion') || str_contains($texto, 'ubicacion') || str_contains($texto, 'donde')) {
        $respuesta = 'L&M PC Computadoras puede ayudarte con ubicacion, rutas y referencias. Si necesitas una direccion exacta o envio, te paso con asistencia.';
    } elseif (str_contains($texto, 'soporte') || str_contains($texto, 'tecnico') || str_contains($texto, 'reparacion') || str_contains($texto, 'arreglo')) {
        $respuesta = 'Para soporte tecnico indica marca, modelo, falla principal y si el equipo enciende. Con esos datos se puede orientar el diagnostico inicial.';
    } elseif (str_contains($texto, 'empresa') || str_contains($texto, 'informacion') || str_contains($texto, 'lympc')) {
        $respuesta = 'L&M PC Computadoras ofrece venta de equipos, accesorios, mantenimiento y reparacion. Si deseas informacion comercial concreta, asistencia puede darte mas detalles.';
    } else {
        $necesitaHumano = true;
        $respuesta = 'Puedo ayudarte con horarios, direcciones, soporte tecnico e informacion de la empresa. Como tu consulta necesita mas contexto, te paso con asistencia.';
    }

    return [$respuesta, $necesitaHumano];
}

if (!$esAsistente) {
    $stmt = $conn->prepare("SELECT * FROM chat_conversaciones WHERE id_usuario = ? ORDER BY fecha_actualizacion DESC LIMIT 1");
    $stmt->execute([$idUsuario]);
    $conversacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conversacion) {
        $stmt = $conn->prepare("INSERT INTO chat_conversaciones (id_usuario, nombre_usuario, correo_usuario, estado, tema) VALUES (?, ?, ?, 'ia', 'Consulta de cliente')");
        $stmt->execute([$idUsuario, $nombreUsuario, $_SESSION['correo'] ?? null]);
        $idConversacion = (int)$conn->lastInsertId();
        $bienvenida = 'Hola, soy la asistencia virtual de L&M PC Computadoras. Puedo ayudarte con soporte tecnico, direcciones, horarios disponibles e informacion de la empresa. En que se le puede ayudar?';
        $stmt = $conn->prepare("INSERT INTO chat_mensajes (id_conversacion, remitente, nombre_remitente, mensaje) VALUES (?, 'ia', 'Asistencia virtual', ?)");
        $stmt->execute([$idConversacion, $bienvenida]);
    } else {
        $idConversacion = (int)$conversacion['id_conversacion'];
    }
} else {
    $idConversacion = isset($_GET['conversacion']) ? (int)$_GET['conversacion'] : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = trim($_POST['mensaje'] ?? '');
    $postConversacion = (int)($_POST['id_conversacion'] ?? $idConversacion);

    if ($mensaje !== '' && $postConversacion > 0) {
        $remitente = $esAsistente ? 'asistente' : 'user';
        $stmt = $conn->prepare("INSERT INTO chat_mensajes (id_conversacion, remitente, nombre_remitente, mensaje) VALUES (?, ?, ?, ?)");
        $stmt->execute([$postConversacion, $remitente, $nombreUsuario, $mensaje]);

        if ($esAsistente) {
            $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = 'atendido' WHERE id_conversacion = ?");
            $stmt->execute([$postConversacion]);
        } else {
            [$respuesta, $necesitaHumano] = respuestaIA($mensaje);
            $stmt = $conn->prepare("INSERT INTO chat_mensajes (id_conversacion, remitente, nombre_remitente, mensaje) VALUES (?, 'ai', 'Asistencia virtual', ?)");
            $stmt->execute([$postConversacion, $respuesta]);
            if ($necesitaHumano) {
                $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = 'pendiente_asistente' WHERE id_conversacion = ?");
                $stmt->execute([$postConversacion]);
            }
        }
    }

    header('Location: ../php/chat.php' . ($esAsistente ? '?conversacion=' . $postConversacion : ''));
    exit();
}

$conversaciones = [];
if ($esAsistente) {
    $stmt = $conn->query("SELECT * FROM chat_conversaciones ORDER BY FIELD(estado, 'pendiente_asistente', 'ia', 'atendido'), fecha_actualizacion DESC");
    $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($idConversacion === 0 && !empty($conversaciones)) {
        $idConversacion = (int)$conversaciones[0]['id_conversacion'];
    }
}

$mensajes = [];
$conversacionActual = null;
if ($idConversacion > 0) {
    $stmt = $conn->prepare("SELECT * FROM chat_conversaciones WHERE id_conversacion = ?");
    $stmt->execute([$idConversacion]);
    $conversacionActual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conversacionActual && (!$esAsistente && (int)$conversacionActual['id_usuario'] !== (int)$idUsuario)) {
        header('Location: ../php/dashboard.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM chat_mensajes WHERE id_conversacion = ? ORDER BY fecha_envio ASC");
    $stmt->execute([$idConversacion]);
    $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/logo.webp">
    <title>Chat con Clientes - L&M PC Computadoras</title>
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
                href="<?php echo $esAsistente ? '../php/dashboard_asistente.php' : '../php/dashboard.php'; ?>"
                style="max-width: 45%; text-align: center;">L&M PC Computadoras</a>
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
                        <li class="nav-item"><a class="nav-link" href="<?php echo $esAsistente ? '../php/dashboard_asistente.php' : '../php/dashboard.php'; ?>">Inicio</a></li>
                        <?php if ($esAsistente): ?>
                        <li class="nav-item"><a class="nav-link" href="../php/lecturadoc.php">Documentos de Pasante</a></li>
                        <li class="nav-item"><a class="nav-link" href="../php/notificaciones.php">Crear Notificaciones</a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="d-flex align-items-center mt-3">
                        <span class="text-white me-3">Buen dia, <b><?php echo htmlspecialchars($nombreUsuario); ?></b></span>
                        <a href="../php/logout.php" class="btn btn-danger btn-sm">Cerrar Sesion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container pasante-hero main-content">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><?php echo $esAsistente ? 'Chat con Clientes' : 'Soporte por Chat'; ?></h1>
                <p><?php echo $esAsistente ? 'Atiende consultas que la asistencia virtual deriva para soporte personalizado.' : 'Consulta sobre soporte tecnico, direcciones, horarios disponibles o informacion de la empresa.'; ?></p>
                <?php if ($conversacionActual): ?>
                <span class="summary-badge"><i class="fas fa-comments"></i> Estado: <?php echo htmlspecialchars($conversacionActual['estado']); ?></span>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <img src="../img/chatCli.webp" alt="Chat con Clientes" class="hero-image">
            </div>
        </div>

        <section class="<?php echo $esAsistente ? 'chat-layout' : ''; ?> mt-4">
            <?php if ($esAsistente): ?>
            <aside class="conversation-list">
                <?php if (empty($conversaciones)): ?>
                <div class="tool-card">No hay conversaciones registradas.</div>
                <?php endif; ?>
                <?php foreach ($conversaciones as $conv): ?>
                <a class="conversation-link <?php echo (int)$conv['id_conversacion'] === $idConversacion ? 'active' : ''; ?>"
                    href="../php/chat.php?conversacion=<?php echo (int)$conv['id_conversacion']; ?>">
                    <strong><?php echo htmlspecialchars($conv['nombre_usuario']); ?></strong>
                    <small class="d-block tool-muted"><?php echo htmlspecialchars($conv['correo_usuario'] ?? 'Sin correo'); ?></small>
                    <span class="status-pill mt-2"><?php echo htmlspecialchars($conv['estado']); ?></span>
                </a>
                <?php endforeach; ?>
            </aside>
            <?php endif; ?>

            <div class="chat-window">
                <div class="chat-messages">
                    <?php if (empty($mensajes)): ?>
                    <p class="tool-muted text-center mt-5">Selecciona una conversacion para revisar los mensajes.</p>
                    <?php endif; ?>
                    <?php foreach ($mensajes as $msg): ?>
                    <div class="chat-bubble <?php echo htmlspecialchars($msg['remitente']); ?>">
                        <strong><?php echo htmlspecialchars($msg['nombre_remitente']); ?></strong>
                        <div><?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?></div>
                        <small><?php echo htmlspecialchars($msg['fecha_envio']); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($idConversacion > 0): ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="id_conversacion" value="<?php echo (int)$idConversacion; ?>">
                    <label class="form-label fw-semibold" for="mensaje">Mensaje</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="3" required
                        placeholder="Escribe tu mensaje..."></textarea>
                    <div class="text-end mt-3">
                        <button class="btn tool-action px-4" type="submit"><i class="fas fa-paper-plane"></i> Enviar</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer mt-5">
        <div class="footer-content container">
            <div>
                <h3>L&M PC Computadoras</h3>
                <p class="footer-note">Chat para consultas de clientes, soporte tecnico y asistencia personalizada.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
