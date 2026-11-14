document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('floatingChat');
    if (!root) return;

    const api = root.dataset.api || '../php/chat_api.php';
    const button = document.getElementById('floatingChatButton');
    const panel = document.getElementById('floatingChatPanel');
    const close = document.getElementById('floatingChatClose');
    const messages = document.getElementById('floatingChatMessages');
    const status = document.getElementById('floatingChatStatus');
    const finish = document.getElementById('floatingChatFinish');
    const rating = document.getElementById('floatingChatRating');
    const ratingValue = document.getElementById('floatingChatRatingValue');
    const ratingNumber = document.getElementById('floatingChatRatingNumber');
    const ratingComment = document.getElementById('floatingChatRatingComment');
    const sendRating = document.getElementById('floatingChatSendRating');
    const optionButtons = document.querySelectorAll('.floating-chat__option');
    let opened = false;
    let pollTimer = null;

    const post = async (data) => {
        const body = new URLSearchParams(data);
        const response = await fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        });
        return await response.json();
    };

    const render = (payload) => {
        if (!payload.ok) {
            const errorBubble = document.createElement('div');
            errorBubble.className = 'floating-chat__bubble floating-chat__bubble--ai';
            errorBubble.textContent = payload.message || 'No se pudo cargar el chat.';
            messages.replaceChildren(errorBubble);
            return;
        }

        const conversation = payload.conversation || {};
        status.textContent = conversation.estado === 'pendiente_asistente'
            ? 'Esperando asistente'
            : conversation.estado === 'atendido'
                ? 'Asistente conectado'
                : 'Asistencia virtual';

        messages.innerHTML = '';
        (payload.messages || []).forEach((item) => {
            const bubble = document.createElement('div');
            const sender = item.remitente === 'user' ? 'user' : item.remitente === 'asistente' ? 'asistente' : 'ai';
            bubble.className = `floating-chat__bubble floating-chat__bubble--${sender}`;
            bubble.textContent = item.mensaje;
            messages.appendChild(bubble);
        });
        messages.scrollTop = messages.scrollHeight;
    };

    const load = async (action = 'bootstrap') => {
        try {
            render(await post({ action }));
        } catch (error) {
            console.error('Error al cargar la conversación del chat:', error);
            render({ ok: false, message: 'No se pudo conectar con el chat.' });
        }
    };

    const startPolling = () => {
        clearInterval(pollTimer);
        pollTimer = setInterval(() => {
            if (opened && rating.hidden) load('poll');
        }, 6000);
    };

    const sendButtonMessage = async (message) => {
        const bubble = document.createElement('div');
        bubble.className = 'floating-chat__bubble floating-chat__bubble--user';
        bubble.textContent = message;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;

        try {
            const result = await post({ action: 'send', message });
            render(result);
        } catch (error) {
            console.error('Error al enviar el mensaje del chat:', error);
            render({ ok: false, message: 'No se pudo enviar el mensaje. Revisa tu conexión e inténtalo nuevamente.' });
        }
    };

    button.addEventListener('click', async () => {
        opened = true;
        panel.hidden = false;
        await load();
        startPolling();
    });

    close.addEventListener('click', () => {
        opened = false;
        panel.hidden = true;
        clearInterval(pollTimer);
    });

    optionButtons.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const message = btn.dataset.message;
            if (!message) return;
            await sendButtonMessage(message);
        });
    });

    finish.addEventListener('click', () => {
        rating.hidden = !rating.hidden;
    });

    ratingValue.addEventListener('input', () => {
        ratingNumber.textContent = ratingValue.value;
    });

    sendRating.addEventListener('click', async () => {
        sendRating.disabled = true;
        try {
            const result = await post({
                action: 'rate',
                rating: ratingValue.value,
                comment: ratingComment.value,
            });
            render(result);
            if (result.ok) {
                rating.hidden = true;
                finish.hidden = true;
                status.textContent = 'Conversacion finalizada';
                clearInterval(pollTimer);
            }
        } catch (error) {
            console.error('Error al calificar la conversación:', error);
            status.textContent = 'No se pudo enviar la calificación';
            render({ ok: false, message: 'No se pudo enviar la calificación. Inténtalo nuevamente.' });
        } finally {
            sendRating.disabled = false;
        }
    });
});
