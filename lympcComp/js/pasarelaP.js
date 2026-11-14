(() => {
    const sdkUrl = 'https://www.paypal.com/sdk/js';
    let pendingOrderId = '';
    let pendingApiUrl = '';
    let pendingPdfUrl = '';
    let confirmingOrder = false;
    const pendingOrderStorageKey = 'lymPcPaypalPendingOrder';

    function savePendingOrder(orderID) {
        try {
            sessionStorage.setItem(pendingOrderStorageKey, orderID);
        } catch (error) {
            console.warn('No se pudo guardar la referencia de pago para recuperación:', error);
        }
    }

    function clearPendingOrder() {
        try {
            sessionStorage.removeItem(pendingOrderStorageKey);
        } catch (error) {
            console.warn('No se pudo limpiar la referencia de pago recuperada:', error);
        }
    }

    function getPendingOrder() {
        try {
            const orderID = sessionStorage.getItem(pendingOrderStorageKey) || '';
            return /^[A-Z0-9]{10,64}$/i.test(orderID) ? orderID : '';
        } catch (error) {
            console.warn('No se pudo leer la referencia de pago pendiente:', error);
            return '';
        }
    }

    function setStatus(message) {
        const status = document.getElementById('paypal-estado');
        if (status) status.textContent = message;
    }

    function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(async response => {
            let data;
            try {
                data = await response.json();
            } catch {
                throw new Error('El servidor no devolvió JSON válido (HTTP ' + response.status + '). Revisa los logs PHP/Laravel.');
            }
            if (!response.ok || !data.ok) {
                const error = new Error(data.msg || 'No se pudo completar la operación.');
                error.reference = data.reference;
                error.orderID = data.orderID;
                throw error;
            }
            return data;
        });
    }

    function loadPayPalSdk(clientId) {
        if (window.paypal) return Promise.resolve();

        const existingScript = document.querySelector('script[data-paypal-sdk]');
        if (existingScript) {
            return new Promise((resolve, reject) => {
                existingScript.addEventListener('load', resolve, { once: true });
                existingScript.addEventListener('error', () => reject(new Error('No se pudo cargar PayPal.')), { once: true });
            });
        }

        const url = new URL(sdkUrl);
        url.search = new URLSearchParams({
            'client-id': clientId,
            currency: 'USD',
            intent: 'capture',
            components: 'buttons'
        }).toString();

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = url.toString();
            script.dataset.paypalSdk = 'true';
            script.onload = resolve;
            script.onerror = () => reject(new Error('No se pudo cargar PayPal. Revisa tu conexión.'));
            document.head.appendChild(script);
        });
    }

    function checkoutUi() {
        const actions = document.getElementById('carrito-acciones');
        const buyButton = document.getElementById('carrito-acciones-comprar');
        if (!actions || !buyButton) return null;

        let paypalContainer = document.getElementById('paypal-button-container');
        if (!paypalContainer) {
            paypalContainer = document.createElement('div');
            paypalContainer.id = 'paypal-button-container';
            buyButton.insertAdjacentElement('afterend', paypalContainer);
        }

        let cancelButton = document.getElementById('paypal-cancelar');
        if (!cancelButton) {
            cancelButton = document.createElement('button');
            cancelButton.id = 'paypal-cancelar';
            cancelButton.type = 'button';
            cancelButton.textContent = 'Cancelar pago';
            paypalContainer.insertAdjacentElement('afterend', cancelButton);
        }

        if (!document.getElementById('paypal-estado')) {
            const status = document.createElement('p');
            status.id = 'paypal-estado';
            status.setAttribute('role', 'status');
            status.setAttribute('aria-live', 'polite');
            cancelButton.insertAdjacentElement('afterend', status);
        }

        let retryButton = document.getElementById('paypal-reintentar');
        if (!retryButton) {
            retryButton = document.createElement('button');
            retryButton.id = 'paypal-reintentar';
            retryButton.type = 'button';
            retryButton.textContent = 'Reintentar confirmar pedido';
            retryButton.hidden = true;
            document.getElementById('paypal-estado').insertAdjacentElement('afterend', retryButton);
        }

        return { actions, buyButton, paypalContainer, cancelButton, retryButton };
    }

    function resetButtons() {
        const ui = checkoutUi();
        if (!ui) return;
        ui.buyButton.hidden = false;
        ui.paypalContainer.hidden = true;
        ui.paypalContainer.replaceChildren();
        ui.cancelButton.hidden = true;
        ui.retryButton.hidden = true;
    }

    window.addEventListener('pageshow', resetButtons);

    async function confirmarOrden(orderID) {
        const ui = checkoutUi();
        if (!ui || !pendingApiUrl || !pendingPdfUrl || confirmingOrder) return;

        pendingOrderId = orderID;
        savePendingOrder(orderID);
        confirmingOrder = true;
        ui.buyButton.hidden = true;
        ui.paypalContainer.hidden = true;
        ui.cancelButton.hidden = true;
        ui.retryButton.hidden = true;
        setStatus('Verificando el pago y registrando tu pedido…');

        try {
            const result = await postJson(pendingApiUrl, {
                action: 'capture-order',
                orderID
            });
            pendingOrderId = '';
            clearPendingOrder();
            setStatus('Pago confirmado. Abriendo el comprobante…');
            window.location.assign(
                pendingPdfUrl + '?id=' + encodeURIComponent(result.id_pedido)
            );
        } catch (error) {
            console.error('Falló la confirmación de la orden PayPal:', error);
            const reference = error.reference ? ' Referencia del servidor: ' + error.reference + '.' : '';
            setStatus(
                (error.message || 'No se pudo confirmar el pago.')
                + ' Orden PayPal: ' + orderID + '.'
                + reference
                + ' No vuelvas a pagar; usa «Reintentar confirmar pedido».'
            );
            ui.retryButton.hidden = false;
        } finally {
            confirmingOrder = false;
        }
    }

    async function iniciarPasarelaPayPal() {
        const ui = checkoutUi();
        const cartBody = document.getElementById('lista-libro');

        const apiUrl = ui?.actions.dataset.apiUrl || 'paypal_api.php';
        pendingApiUrl = apiUrl;
        pendingPdfUrl = ui?.actions.dataset.pdfUrl || 'generar_pedido_pdf.php';
        const recoverableOrder = getPendingOrder();
        if (recoverableOrder) {
            pendingOrderId = recoverableOrder;
            setStatus('Se encontró un pago anterior pendiente de confirmar. Reintentando el registro; no vuelvas a pagar.');
            await confirmarOrden(recoverableOrder);
            return;
        }

        if (!ui || !cartBody || !cartBody.querySelector('tr')) {
            setStatus('El carrito está vacío.');
            return;
        }

        ui.buyButton.hidden = true;
        ui.paypalContainer.hidden = false;
        ui.cancelButton.hidden = false;
        setStatus('Conectando con PayPal…');

        try {
            const recovered = await postJson(apiUrl, { action: 'recover-completed' });
            if (recovered.orderID) {
                pendingOrderId = recovered.orderID;
                savePendingOrder(recovered.orderID);
                setStatus('Encontramos un pago PayPal completado que aún no tenía pedido asociado. Recuperando el pedido; no vuelvas a pagar.');
                await confirmarOrden(recovered.orderID);
                return;
            }

            const config = await postJson(apiUrl, { action: 'config' });
            await loadPayPalSdk(config.clientId);
            if (!window.paypal) throw new Error('PayPal no pudo inicializarse.');

            const buttons = window.paypal.Buttons({
                createOrder: async () => {
                    const result = await postJson(apiUrl, { action: 'create-order' });
                    return result.orderID;
                },
                onApprove: async data => {
                    await confirmarOrden(data.orderID);
                },
                onCancel: () => {
                    setStatus('Pago cancelado. No se registró ningún pedido.');
                    resetButtons();
                },
                onError: error => {
                    console.error('Error en el checkout PayPal:', error);
                    if (pendingOrderId) {
                        confirmarOrden(pendingOrderId);
                        return;
                    }
                    setStatus(error?.message || 'PayPal no pudo iniciar el checkout. No se confirmó ningún pago.');
                    resetButtons();
                }
            });

            if (!buttons.isEligible()) {
                throw new Error('PayPal no ofrece un método de pago disponible para esta cuenta.');
            }
            await buttons.render('#paypal-button-container');
            setStatus('Elige PayPal para continuar con tu compra.');
        } catch (error) {
            console.error('No se pudo iniciar PayPal:', error);
            setStatus(error.message || 'No se pudo iniciar PayPal. Intenta nuevamente.');
            resetButtons();
        }
    }

    window.iniciarPasarelaPayPal = iniciarPasarelaPayPal;

    document.addEventListener('click', event => {
        if (event.target.closest('#paypal-cancelar')) {
            event.preventDefault();
            if (pendingOrderId) return;
            pendingOrderId = '';
            setStatus('Checkout cerrado. No se registró ningún pedido.');
            resetButtons();
        }
        if (event.target.closest('#paypal-reintentar')) {
            event.preventDefault();
            if (pendingOrderId) confirmarOrden(pendingOrderId);
        }
    });
})();
