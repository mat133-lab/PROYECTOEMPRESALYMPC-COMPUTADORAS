(() => {
    const dialog = document.getElementById('navigationConfirmation');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const message = document.getElementById('navigationConfirmationMessage');
    const allowButton = dialog.querySelector('[data-navigation-allow]');
    const cancelButton = dialog.querySelector('[data-navigation-cancel]');
    let pendingNavigation = null;

    const describeDestination = (link, url) => {
        const path = url.pathname.toLowerCase();
        if (path.endsWith('/logout.php')) {
            return {
                title: '¿Deseas cerrar sesión?',
                message: 'Se cerrará tu sesión y volverás a la página beta.',
            };
        }

        const label = (link.getAttribute('aria-label') || link.innerText || link.title || '').trim();
        if (url.hostname === 'wa.me' || url.hostname === 'api.whatsapp.com') {
            return { title: '¿Abrir WhatsApp?', message: 'Se abrirá el chat de WhatsApp de la empresa.' };
        }
        if (url.hostname === 't.me' || url.hostname === 'telegram.me') {
            return { title: '¿Abrir Telegram?', message: 'Se abrirá el chat del bot de Telegram.' };
        }

        return {
            title: '¿Deseas continuar?',
            message: label ? `Vas a abrir: ${label}` : `Vas a dirigirte a ${url.hostname || url.pathname}.`,
        };
    };

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        const clickTarget = event.target instanceof Element ? event.target : event.target?.parentElement;
        const link = clickTarget?.closest('a[href]');
        if (!link || link.closest('#navigationConfirmation') || link.hasAttribute('download')) return;
        if (link.dataset.confirmNavigation === 'false'
            || link.hasAttribute('data-bs-toggle')
            || link.hasAttribute('data-toggle')
            || link.hasAttribute('data-bs-dismiss')) return;

        const rawHref = link.getAttribute('href')?.trim();
        if (!rawHref || rawHref === '#' || /^(javascript:|mailto:|tel:)/i.test(rawHref)) return;

        let destination;
        try {
            destination = new URL(rawHref, window.location.href);
        } catch {
            return;
        }

        if (destination.origin === window.location.origin
            && destination.pathname === window.location.pathname
            && destination.search === window.location.search
            && destination.hash) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const prompt = describeDestination(link, destination);
        document.getElementById('navigationConfirmationTitle').textContent = prompt.title;
        message.textContent = prompt.message;
        pendingNavigation = {
            href: destination.href,
            target: link.target && link.target !== '_self' ? link.target : '',
        };
        dialog.showModal();
        cancelButton.focus();
    }, true);

    cancelButton.addEventListener('click', () => {
        pendingNavigation = null;
        dialog.close();
    });

    allowButton.addEventListener('click', () => {
        if (!pendingNavigation) {
            dialog.close();
            return;
        }

        const { href, target } = pendingNavigation;
        pendingNavigation = null;
        dialog.close();
        if (target) {
            window.open(href, target, 'noopener,noreferrer');
        } else {
            window.location.assign(href);
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            pendingNavigation = null;
            dialog.close();
        }
    });

    dialog.addEventListener('close', () => {
        pendingNavigation = null;
    });
})();
