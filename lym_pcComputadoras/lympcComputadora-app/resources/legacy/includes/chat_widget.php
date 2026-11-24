<?php
$rolChatWidget = strtolower($_SESSION['rol'] ?? 'usuario');
$mostrarChatWidget = isset($_SESSION['usuario']) && !in_array($rolChatWidget, ['admin', 'tecnico', 'encargado', 'pasante', 'asistente'], true);
$nombreBotTelegram = function_exists('config')
    ? (string) config('services.telegram.bot_username', '')
    : (string) (function_exists('env') ? env('TELEGRAM_BOT_USERNAME', '') : '');
$nombreBotTelegram = ltrim(trim($nombreBotTelegram), '@');
$urlBotTelegram = $nombreBotTelegram !== '' ? 'https://t.me/' . rawurlencode($nombreBotTelegram) : '';
?>
<link rel="stylesheet" href="<?= legacy_asset_url('css/chat_widget.css') ?>">

<div class="floating-contact-actions">
    <a class="floating-whatsapp" href="https://wa.me/593983093667" target="_blank" rel="noopener noreferrer" aria-label="Escribir por WhatsApp a L&amp;M PC Computadoras" title="WhatsApp de L&amp;M PC Computadoras">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    </a>

<?php if ($mostrarChatWidget): ?>
<div class="floating-chat" id="floatingChat" data-api="../php/chat_api.php">
    <button class="floating-chat__button" type="button" id="floatingChatButton" aria-label="Abrir chat de soporte">
        <img src="../img/bot.jpg" alt="" aria-hidden="true">
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

        <form class="floating-chat__form" id="floatingChatForm">
            <input type="text" id="floatingChatInput" placeholder="Escribe tu consulta..." autocomplete="off" required>
            <button type="submit" aria-label="Enviar mensaje">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>

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
            <?php if ($urlBotTelegram !== ''): ?>
                <a href="<?= htmlspecialchars($urlBotTelegram, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir bot en Telegram</a>
            <?php endif; ?>
        </footer>
    </section>
</div>
<?php endif; ?>
</div>

<?php if ($mostrarChatWidget): ?>
<script src="<?= legacy_asset_url('js/chat_widget.js') ?>"></script>
<?php endif; ?>
