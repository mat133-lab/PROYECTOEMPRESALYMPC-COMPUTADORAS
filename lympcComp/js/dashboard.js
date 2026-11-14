const dashboardScriptUrl = document.currentScript?.src;
let pasarelaScriptPromise = null;

function cargarPasarelaPayPal() {
    if (typeof window.iniciarPasarelaPayPal === 'function') return Promise.resolve();
    if (!pasarelaScriptPromise) {
        pasarelaScriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = dashboardScriptUrl
                ? new URL('pasarelaP.js', dashboardScriptUrl).toString()
                : 'pasarelaP.js';
            script.onload = resolve;
            script.onerror = () => reject(new Error('No se pudo cargar el módulo de PayPal.'));
            document.head.appendChild(script);
        }).catch(error => {
            pasarelaScriptPromise = null;
            throw error;
        });
    }
    return pasarelaScriptPromise;
}

document.addEventListener('DOMContentLoaded', cargarCarritoServidor);

async function cargarCarritoServidor() {
    const cartBody = document.getElementById('lista-libro');
    if (!cartBody) return;

    try {
        const response = await fetch('cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'list' })
        });
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.msg || 'No se pudo cargar el carrito.');
        pintarCarrito(data.items);
    } catch (error) {
        console.error('Error al cargar el carrito:', error);
    }
}

function pintarCarrito(items) {
    const cartBody = document.getElementById('lista-libro');
    const totalEl = document.getElementById('total');
    const actions = document.getElementById('carrito-acciones');
    if (!cartBody || !totalEl) return;

    cartBody.replaceChildren();
    let total = 0;

    items.forEach(item => {
        const product = item.producto;
        const quantity = Number(item.cantidad);
        const price = Number(product.precio);
        const row = document.createElement('tr');
        row.dataset.id = product.id;

        const imageCell = document.createElement('td');
        const image = document.createElement('img');
        image.src = '../img/' + encodeURIComponent(String(product.imagen).split(/[\\/]/).pop());
        image.alt = product.nombre;
        image.loading = 'lazy';
        image.style.cssText = 'width:50px;border-radius:5px;object-fit:cover';
        imageCell.appendChild(image);

        const nameCell = document.createElement('td');
        nameCell.textContent = product.nombre;
        const serialCell = document.createElement('td');
        serialCell.textContent = product.serie;
        const dateCell = document.createElement('td');
        dateCell.textContent = product.fecha;
        const quantityCell = document.createElement('td');
        quantityCell.className = 'cart-qty text-center';
        quantityCell.textContent = String(quantity);
        const priceCell = document.createElement('td');
        priceCell.textContent = '$' + price.toFixed(2);

        row.append(imageCell, nameCell, serialCell, dateCell, quantityCell, priceCell);
        cartBody.appendChild(row);
        total += price * quantity;

        document.querySelectorAll('.product-units[data-id="' + product.id + '"]').forEach(stockElement => {
            stockElement.textContent = String(item.stock);
        });
    });

    totalEl.textContent = '$' + total.toFixed(2);
    if (actions) actions.classList.toggle('disabled', items.length === 0);
}

document.addEventListener('click', async event => {
    const addButton = event.target.closest('.agregar-libro');
    if (addButton) {
        event.preventDefault();
        addButton.disabled = true;
        try {
            const response = await fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    product_id: addButton.dataset.id,
                    qty: '1',
                    action: 'add'
                })
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.msg || 'No se pudo agregar el producto.');

            document.querySelectorAll('.product-units[data-id="' + data.producto.id + '"]').forEach(stockElement => {
                stockElement.textContent = String(data.stock);
            });
            await cargarCarritoServidor();
        } catch (error) {
            alert(error.message || 'No se pudo agregar el producto al carrito.');
        } finally {
            addButton.disabled = false;
        }
        return;
    }

    if (event.target.closest('#carrito-acciones-comprar')) {
        event.preventDefault();
        try {
            await cargarPasarelaPayPal();
        } catch (error) {
            alert('No se pudo cargar la pasarela PayPal. Recarga la página e inténtalo nuevamente.');
            console.error(error);
            return;
        }
        if (typeof window.iniciarPasarelaPayPal !== 'function') {
            alert('No se pudo inicializar la pasarela PayPal.');
            return;
        }
        window.iniciarPasarelaPayPal();
        return;
    }

    if (event.target.closest('#carrito-acciones-vaciar')) {
        event.preventDefault();
        try {
            const response = await fetch('cart_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'clear' })
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.msg || 'No se pudo vaciar el carrito.');
            localStorage.removeItem('carritoHTML');
            localStorage.removeItem('carritoTotal');
            location.reload();
        } catch (error) {
            alert(error.message || 'No se pudo vaciar el carrito.');
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    let currentItem = 4;
    const boxes = document.querySelectorAll('.box');
    const loadMore = document.getElementById('load-more');
    const noResultMessage = document.getElementById('no-results');

    function updateLoadMoreView() {
        boxes.forEach((box, index) => {
            box.style.display = index < currentItem ? '' : 'none';
        });
        if (loadMore) loadMore.style.display = currentItem >= boxes.length ? 'none' : 'inline-block';
    }

    updateLoadMoreView();
    if (loadMore) {
        loadMore.addEventListener('click', () => {
            currentItem += 4;
            updateLoadMoreView();
        });
    }

    const searchInput = document.getElementById('search-input');
    const mobileSearchInput = document.getElementById('search-input-mobile');

    function filterProducts(value) {
        const term = value.toLowerCase().trim();
        let found = false;

        if (!term) {
            updateLoadMoreView();
            if (noResultMessage) noResultMessage.style.display = 'none';
            return;
        }

        if (loadMore) loadMore.style.display = 'none';
        boxes.forEach(box => {
            const name = box.querySelector('.product-name');
            const matches = name && name.textContent.toLowerCase().includes(term);
            box.style.display = matches ? '' : 'none';
            found = found || Boolean(matches);
        });
        if (noResultMessage) noResultMessage.style.display = found ? 'none' : 'block';
    }

    if (searchInput) searchInput.addEventListener('input', event => filterProducts(event.target.value));
    if (mobileSearchInput) mobileSearchInput.addEventListener('input', event => filterProducts(event.target.value));

    document.querySelectorAll('form[role="search"] .btn-success').forEach(button => {
        button.addEventListener('click', () => {
            if (searchInput) searchInput.blur();
            if (mobileSearchInput) mobileSearchInput.blur();
            const destination = document.querySelector('.products') || document.getElementById('product-container');
            if (destination) destination.scrollIntoView({ behavior: 'smooth' });
        });
    });
});

function confirmarBorrado(id_producto) {
    if (confirm('¿Seguro que deseas Borrar?')) {
        window.location.href = '../php/eliminar.php?id=' + encodeURIComponent(id_producto);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.btn-editar');
    const modalElement = document.getElementById('modalEditarUnico');
    if (!editButtons.length || !modalElement || !window.bootstrap) return;

    const editModal = new bootstrap.Modal(modalElement);
    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit_id_producto').value = this.dataset.id;
            document.getElementById('edit_nombre').value = this.dataset.nombre;
            document.getElementById('edit_serie').value = this.dataset.serie;
            document.getElementById('edit_fecha').value = this.dataset.fecha;
            document.getElementById('edit_unidades').value = this.dataset.unidades;
            document.getElementById('edit_precio').value = this.dataset.precio;
            document.getElementById('edit_categoria').value = this.dataset.categoria;
            editModal.show();
        });
    });
});
