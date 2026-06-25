<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/chat_helpers.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /php/login.php');
    exit();
}

$rol = strtolower($_SESSION['rol'] ?? 'usuario');
$esAsistente = in_array($rol, ['asistente', 'admin', 'tecnico'], true);
$idUsuario = $_SESSION['id_usuario'] ?? null;
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';

chatEnsureTables($conn);

if (!$esAsistente && isset($_GET['finalizado'])) {
    $idConversacion = 0;
} elseif (!$esAsistente) {
    $idConversacion = chatGetOrCreateUserConversation($conn, $idUsuario, $nombreUsuario, $_SESSION['correo'] ?? null, isset($_GET['nuevo']));
} else {
    $idConversacion = isset($_GET['conversacion']) ? (int)$_GET['conversacion'] : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'mensaje';
    $mensaje = trim($_POST['mensaje'] ?? '');
    $postConversacion = (int)($_POST['id_conversacion'] ?? $idConversacion);

    if ($accion === 'calificar' && !$esAsistente && $postConversacion > 0) {
        chatRateConversation($conn, $postConversacion, $idUsuario, $_POST['calificacion'] ?? 0, $_POST['comentario_calificacion'] ?? '');
        header('Location: /php/chat.php?finalizado=1');
        exit();
    }

    if ($accion === 'mensaje' && $mensaje !== '' && $postConversacion > 0) {
        if ($esAsistente) {
            chatAddMessage($conn, $postConversacion, 'asistente', $nombreUsuario, $mensaje);
            $stmt = $conn->prepare("UPDATE chat_conversaciones SET estado = 'atendido' WHERE id_conversacion = ?");
            $stmt->execute([$postConversacion]);
        } else {
            chatHandleUserMessage($conn, $postConversacion, $idUsuario, $nombreUsuario, $mensaje);
        }
    }

    header('Location: /php/chat.php' . ($esAsistente ? '?conversacion=' . $postConversacion : ''));
    exit();
}

$conversaciones = [];
if ($esAsistente) {
    $stmt = $conn->query("SELECT * FROM chat_conversaciones ORDER BY CASE estado
        WHEN 'pendiente_asistente' THEN 1
        WHEN 'ia' THEN 2
        WHEN 'atendido' THEN 3
        WHEN 'finalizado' THEN 4
        ELSE 5
    END, fecha_actualizacion DESC");
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
        header('Location: /php/dashboard.php');
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
                <?php if ($esAsistente && !empty($conversacionActual['calificacion'])): ?>
                <span class="summary-badge"><i class="fas fa-star"></i> Calificacion: <?php echo (int)$conversacionActual['calificacion']; ?>/10</span>
                <?php endif; ?>
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
                <div class="tool-card">
                    <h2 class="tool-title h5">Sin conversaciones por ahora</h2>
                    <p class="tool-muted mb-0">Cuando un cliente escriba desde el icono flotante del dashboard, aparecera aqui para atenderlo.</p>
                </div>
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
                <?php if (!$esAsistente && isset($_GET['finalizado'])): ?>
                <div class="tool-card text-center mb-0">
                    <h2 class="tool-title h4">Conversacion finalizada</h2>
                    <p class="tool-muted">Gracias por calificar tu experiencia. Puedes iniciar una nueva consulta cuando lo necesites.</p>
                    <a class="btn tool-action px-4" href="../php/chat.php?nuevo=1">
                        <i class="fas fa-plus"></i> Nueva conversacion
                    </a>
                </div>
                <?php else: ?>
                <div class="chat-messages">
                    <?php if (empty($mensajes)): ?>
                    <div class="text-center mt-5">
                        <p class="tool-muted mb-2">Selecciona una conversacion para revisar los mensajes.</p>
                        <?php if ($esAsistente): ?>
                        <a class="btn btn-sm tool-action" href="../php/chat.php"><i class="fas fa-rotate"></i> Actualizar</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($mensajes as $msg): ?>
                    <div class="chat-bubble <?php echo htmlspecialchars($msg['remitente']); ?>">
                        <strong><?php echo htmlspecialchars($msg['nombre_remitente']); ?></strong>
                        <div><?php echo nl2br(htmlspecialchars($msg['mensaje'])); ?></div>
                        <small><?php echo htmlspecialchars($msg['fecha_envio']); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($esAsistente && $conversacionActual && !empty($conversacionActual['comentario_calificacion'])): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <strong>Comentario del cliente:</strong>
                    <?php echo htmlspecialchars($conversacionActual['comentario_calificacion']); ?>
                </div>
                <?php endif; ?>

                <?php if ($idConversacion > 0 && (!$conversacionActual || $conversacionActual['estado'] !== 'finalizado')): ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="accion" value="mensaje">
                    <input type="hidden" name="id_conversacion" value="<?php echo (int)$idConversacion; ?>">
                    <label class="form-label fw-semibold" for="mensaje">Mensaje</label>
                    <textarea class="form-control" id="mensaje" name="mensaje" rows="3" required
                        placeholder="Escribe tu mensaje..."></textarea>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <?php if (!$esAsistente): ?>
                        <button class="btn tool-secondary px-4" type="button" data-bs-toggle="modal" data-bs-target="#modalCalificacion">
                            <i class="fas fa-check-circle"></i> Finalizar conversacion
                        </button>
                        <?php endif; ?>
                        <button class="btn tool-action px-4" type="submit"><i class="fas fa-paper-plane"></i> Enviar</button>
                    </div>
                </form>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php if (!$esAsistente && $idConversacion > 0): ?>
    <div class="modal fade" id="modalCalificacion" tabindex="-1" aria-labelledby="modalCalificacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rating-modal">
                <form method="POST">
                    <input type="hidden" name="accion" value="calificar">
                    <input type="hidden" name="id_conversacion" value="<?php echo (int)$idConversacion; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCalificacionLabel">Califica tu experiencia</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label fw-semibold" for="calificacion">Del 1 al 10, como fue tu experiencia?</label>
                        <input class="form-range" type="range" min="1" max="10" value="10" id="calificacion" name="calificacion"
                            oninput="document.getElementById('ratingValue').textContent = this.value">
                        <div class="rating-number" id="ratingValue">10</div>

                        <label class="form-label fw-semibold mt-3" for="comentario_calificacion">Comentario opcional</label>
                        <textarea class="form-control" id="comentario_calificacion" name="comentario_calificacion" rows="3"
                            placeholder="Cuéntanos que podemos mejorar..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn tool-secondary" data-bs-dismiss="modal">Volver al chat</button>
                        <button type="submit" class="btn tool-action">Enviar calificacion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
    <?php if ($esAsistente): ?>
    <script>
        setInterval(() => {
            const active = document.activeElement;
            const isTyping = active && (active.tagName === 'TEXTAREA' || active.tagName === 'INPUT');
            if (!isTyping) {
                window.location.reload();
            }
        }, 15000);
    </script>
    <?php endif; ?>
</body>

</html>
