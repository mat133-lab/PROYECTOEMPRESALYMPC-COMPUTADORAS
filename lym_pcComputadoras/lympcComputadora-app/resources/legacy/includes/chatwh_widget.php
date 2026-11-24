<?php
$rolChatWidget = strtolower($_SESSION['rol'] ?? 'usuario');
$mostrarChatWidget = isset($_SESSION['usuario']) && !in_array($rolChatWidget, ['admin', 'tecnico', 'encargado', 'pasante', 'asistente'], true);
?>
<?php if ($mostrarChatWidget): ?>
<link rel="stylesheet" href="<?= legacy_asset_url('css/chat_widget.css') ?>">

<div class="floating-chat" id="floatingChat" data-api="../php/chat_api.php">
<!--Redirige al chat de whatsapp de la empresa-->
    <button class="floating-chat__button" type="button" id="floatingChatButton" aria-label="Abrir chat de soporte" link="+593 98 309 3667">
        <i class="fas fa-comments"></i>
    </button>
</div>

<script src="<?= legacy_asset_url('js/chat_widget.js') ?>"></script>
<?php endif; ?>