<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/chat_helpers.php';

header('Content-Type: application/json; charset=utf-8');

function chatJson($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    return;
}

if (!isset($_SESSION['usuario'], $_SESSION['id_usuario'])) {
    chatJson(['ok' => false, 'message' => 'Debe iniciar sesion para usar el chat.'], 401);
}

$rol = strtolower($_SESSION['rol'] ?? 'usuario');
if (in_array($rol, ['admin', 'tecnico', 'encargado', 'pasante', 'asistente'], true)) {
    chatJson(['ok' => false, 'message' => 'El widget de chat esta disponible para usuarios clientes.'], 403);
}

chatEnsureTables($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? 'bootstrap';
$idUsuario = (int)$_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
$correoUsuario = $_SESSION['correo'] ?? null;

if ($action === 'new') {
    $idConversacion = chatGetOrCreateUserConversation($conn, $idUsuario, $nombreUsuario, $correoUsuario, true);
} else {
    $idConversacion = chatGetOrCreateUserConversation($conn, $idUsuario, $nombreUsuario, $correoUsuario);
}

if ($action === 'send') {
    $mensaje = trim($_POST['message'] ?? '');
    if ($mensaje === '') {
        chatJson(['ok' => false, 'message' => 'Escribe un mensaje para enviarlo.'], 422);
    }

    chatHandleUserMessage($conn, $idConversacion, $idUsuario, $nombreUsuario, $mensaje);
}

if ($action === 'rate') {
    chatRateConversation(
        $conn,
        $idConversacion,
        $idUsuario,
        $_POST['rating'] ?? 0,
        $_POST['comment'] ?? ''
    );

    chatJson([
        'ok' => true,
        'conversation' => chatGetConversation($conn, $idConversacion),
        'messages' => chatGetMessages($conn, $idConversacion),
    ]);
}

$conversacion = chatGetConversation($conn, $idConversacion);
if (!$conversacion || (int)$conversacion['id_usuario'] !== $idUsuario) {
    chatJson(['ok' => false, 'message' => 'Conversacion no disponible.'], 404);
}

chatJson([
    'ok' => true,
    'conversation' => $conversacion,
    'messages' => chatGetMessages($conn, $idConversacion),
]);

