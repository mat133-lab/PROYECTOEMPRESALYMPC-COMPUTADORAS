document.addEventListener('DOMContentLoaded', cargarStorage);

function sincronizarStorage() {
    const cartBody = document.getElementById('lista-libro');
    const totalEl = document.getElementById('total');
    if (cartBody && totalEl) {
        localStorage.setItem('carritoHTML', cartBody.innerHTML);
        localStorage.setItem('carritoTotal', totalEl.textContent);
    }
}

function cargarStorage() {
    const cartBody = document.getElementById('lista-libro');
    const totalEl = document.getElementById('total');
    const carritoAcciones = document.getElementById('carrito-acciones');

    if (cartBody && totalEl) {
        cartBody.innerHTML = localStorage.getItem('carritoHTML') || '';
        totalEl.textContent = localStorage.getItem('carritoTotal') || '$0';

        if (cartBody.innerHTML.trim() !== '') {
            if (carritoAcciones) carritoAcciones.classList.remove('disabled');
        }
    }
}

document.addEventListener('click', function (e) {

    // --- AGREGAR PRODUCTO ---
    if (e.target.matches('.agregar-libro')) {
        e.preventDefault();
        const id = e.target.dataset.id;

        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: id, qty: 1, action: 'add' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                const el = document.querySelector('.product-units[data-id="' + id + '"]');
                if (el) el.textContent = data.stock;

                const totalEl = document.getElementById('total');
                if (totalEl) {
                    let current = parseFloat(totalEl.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    current = current + parseFloat(data.producto.precio);
                    totalEl.textContent = '$' + current.toFixed(2);
                }
                
                const cartBody = document.getElementById('lista-libro'); // Corregido
                if (cartBody) {
                    let filaExistente = cartBody.querySelector(`tr[data-id="${data.producto.id}"]`);

                    if (filaExistente) {
                        let tdUnidades = filaExistente.querySelector('.cart-qty');
                        tdUnidades.textContent = parseInt(tdUnidades.textContent) + 1;
                    } else {
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

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.remove('disabled');

                sincronizarStorage(); // Guardamos

            } else {
                alert(data.msg);
            }
        }).catch(err => {
            alert('Error en la solicitud. Revisa la consola.');
            console.error(err);
        });
    }

    // --- PROCESAR COMPRA ---
    if (e.target.id === 'carrito-acciones-comprar') {
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'purchase' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                alert(data.msg);

                const totalEl = document.getElementById('total');
                if (totalEl) totalEl.textContent = '$0';

                const cartBody = document.getElementById('lista-libro'); // Corregido
                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');
                
                sincronizarStorage(); // Borramos
            } else {
                alert(data.msg);
            }
        }).catch(err => {
            alert('Error en la compra');
            console.error(err);
        });
    }

    // --- VACIAR CARRITO ---
    if (e.target.id === 'carrito-acciones-vaciar') {
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'clear' })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                const totalEl = document.getElementById('total');
                if (totalEl) totalEl.textContent = '$0';

                const cartBody = document.getElementById('lista-libro'); // Corregido
                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');

                sincronizarStorage(); // Borramos memoria
            }
        }).catch(err => { console.error(err); });
    }
});

// ==========================================
// 3. LÓGICA DEL BOTÓN "CARGAR MÁS OFERTAS"
// ==========================================
document.addEventListener('DOMContentLoaded', function () {
    let currentItem = 4; // Productos a mostrar inicialmente
    const btnLoadMore = document.getElementById('load-more');
    const cajas = document.querySelectorAll('.box'); // <-- AHORA BUSCA LA CLASE .box CORRECTAMENTE

    // Ocultar al principio los productos del 5 en adelante
    for(let i = currentItem; i < cajas.length; i++){
        cajas[i].style.display = 'none';
    }

    // Si hay 4 productos o menos, no necesitamos el botón
    if (cajas.length <= currentItem && btnLoadMore) {
        btnLoadMore.style.display = 'none';
    }

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function () {
            // Mostrar los siguientes 4 productos
            for (let i = currentItem; i < currentItem + 4; i++) {
                if (cajas[i]) {
                    cajas[i].style.display = ''; // Se borra el display:none para que se vea
                }
            }
            currentItem += 4;

            // Ocultar el botón si ya mostramos todos
            if (currentItem >= cajas.length) {
                btnLoadMore.style.display = 'none';
            }
        });
    }
});