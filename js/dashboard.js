document.addEventListener('DOMContentLoaded', cargarStorage);

function sincronizarStorage() {
    const cartBody = document.querySelector('#lista-libro tbody');
    const totalEl = document.getElementById('total');
    if (cartBody && totalEl) {
        // Guarda el HTML de la tabla y el texto del total en la memoria del navegador
        localStorage.setItem('carritoHTML', cartBody.innerHTML);
        localStorage.setItem('carritoTotal', totalEl.textContent);
    }
}

function cargarStorage() {
    const cartBody = document.querySelector('#lista-libro tbody');
    const totalEl = document.getElementById('total');
    const carritoAcciones = document.getElementById('carrito-acciones');

    if (cartBody && totalEl) {
        // Recupera la información de la memoria (o lo deja vacío si no hay nada)
        cartBody.innerHTML = localStorage.getItem('carritoHTML') || '';
        totalEl.textContent = localStorage.getItem('carritoTotal') || '$0';

        // Si hay productos recuperados, activa los botones de comprar/vaciar
        if (cartBody.innerHTML.trim() !== '') {
            if (carritoAcciones) carritoAcciones.classList.remove('disabled');
        }
    }
}
document.addEventListener('click', function (e) {

    // AGREGAR PRODUCTO AL CARRITO
    if (e.target.matches('.agregar-libro')) {
        e.preventDefault();
        const id = e.target.dataset.id;

        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: id, qty: 1, action: 'add' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                // Actualizar cantidad visible en el catálogo
                const el = document.querySelector('.product-units[data-id="' + id + '"]');
                if (el) el.textContent = data.stock;

                // Actualizar total del carrito
                const totalEl = document.getElementById('total');
                if (totalEl) {
                    let current = parseFloat(totalEl.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    current = current + parseFloat(data.producto.precio);
                    totalEl.textContent = '$' + current.toFixed(2);
                }
                const cartBody = document.querySelector('#lista-libro');
                if (cartBody) {
                    // Verificamos si la fila del producto ya existe
                    let filaExistente = cartBody.querySelector(`tr[data-id="${data.producto.id}"]`);

                    if (filaExistente) {
                        // Si ya existe, le sumamos 1 a la columna de unidades
                        let tdUnidades = filaExistente.querySelector('.cart-qty');
                        tdUnidades.textContent = parseInt(tdUnidades.textContent) + 1;
                    } else {
                        // Si no existe, creamos la fila completa
                        const row = document.createElement('tr');
                        row.setAttribute('data-id', data.producto.id);
                        row.innerHTML = `
                            <td><img src="../img/${data.producto.imagen}" style="width: 50px; border-radius: 5px; object-fit: cover;"></td>
                            <td>${data.producto.nombre}</td>
                            <td>${data.producto.serie}</td>
                            <td>${data.producto.fecha}</td>
                            <td class="cart-qty text-center">1</td>
                            <td>$${parseFloat(data.producto.precio).toFixed(2)}</td>
                        `;
                        cartBody.appendChild(row);
                    }
                }

                // Habilitar botones del carrito
                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.remove('disabled');

            } else {
                alert(data.msg);
            }
        }).catch(err => {
            alert('Error en la solicitud. Revisa la consola.');
            console.error(err);
        });
    }

    // PROCESAR COMPRA

    if (e.target.id === 'carrito-acciones-comprar') {
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'purchase' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                alert(data.msg);

                // Limpiar Total y Tabla
                const totalEl = document.getElementById('total');
                if (totalEl) totalEl.textContent = '$0';

                const cartBody = document.querySelector('#lista-libro');
                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');
            } else {
                alert(data.msg);
            }
        }).catch(err => {
            alert('Error en la compra');
            console.error(err);
        });
    }

    // VACIAR CARRITO

    if (e.target.id === 'carrito-acciones-vaciar') {
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'clear' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                // Limpiar Total y Tabla
                const totalEl = document.getElementById('total');
                if (totalEl) totalEl.textContent = '$0';

                const cartBody = document.querySelector('#lista-libro');
                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');

            }
        }).catch(err => { console.error(err); });
    }
});