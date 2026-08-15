<?php
$rolChatWidget = strtolower($_SESSION['rol'] ?? 'usuario');
$mostrarChatWidget = isset($_SESSION['usuario']) && !in_array($rolChatWidget, ['admin', 'tecnico', 'encargado', 'pasante', 'asistente'], true);
?>
<?php if ($mostrarChatWidget): ?>
<link rel="stylesheet" href="../css/chat_widget.css">

<div class="floating-chat" id="floatingChat" data-api="../php/chat_api.php">
    <button class="floating-chat__button" type="button" id="floatingChatButton" aria-label="Abrir chat de soporte">
        <i class="fas fa-comments"></i>
    </button>

    <section class="floating-chat__panel" id="floatingChatPanel" aria-label="Chat de soporte" hidden>
        <header class="floating-chat__header">
            <div>
                <strong>Soporte L&M PC</strong>
                <span id="floatingChatStatus">Asistencia virtual</span>
            </div>
            <button type="button" id="floatingChatClose" aria-label="Cerrar chat">
                <i class="fas fa-times"></i>
            </button>
        </header>

        <div class="floating-chat__messages" id="floatingChatMessages"></div>

        <div class="floating-chat__buttons" id="floatingChatButtons">
            <button class="floating-chat__option" data-message="1">Soporte Tecnico</button>
            <button class="floating-chat__option" data-message="2">Horarios</button>
            <button class="floating-chat__option" data-message="3">Direccion</button>
            <button class="floating-chat__option" data-message="4">Informacion</button>
            <button class="floating-chat__option" data-message="5">Hablar con Asesor</button>
        </div>

        <div class="floating-chat__rating" id="floatingChatRating" hidden>
            <p>Califica tu experiencia del 1 al 10</p>
            <input type="range" id="floatingChatRatingValue" min="1" max="10" value="10">
            <strong id="floatingChatRatingNumber">10</strong>
            <textarea id="floatingChatRatingComment" rows="2" placeholder="Comentario opcional"></textarea>
            <button type="button" id="floatingChatSendRating">Enviar calificacion</button>
        </div>

        <footer class="floating-chat__footer">
            <button type="button" id="floatingChatFinish">Finalizar y calificar</button>
            <a href="../php/chat.php">Abrir pantalla completa</a>
        </footer>
    </section>
</div>

<script src="../js/chat_widget.js"></script>
<?php endif; ?>
