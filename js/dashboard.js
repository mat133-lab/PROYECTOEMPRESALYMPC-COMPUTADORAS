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

                const cartBody = document.getElementById('lista-libro'); //a mostrar productos por su id en el carrito
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

        // Recopilar los datos del carrito desde el HTML
        const cartBody = document.getElementById('lista-libro');
        const rows = cartBody.querySelectorAll('tr');
        const carrito = [];

        rows.forEach(row => {
            const id = row.dataset.id;
            const qtyCell = row.querySelector('.cart-qty');
            if (id && qtyCell) {
                carrito.push({
                    id: parseInt(id),
                    cantidad: parseInt(qtyCell.textContent)
                });
            }
        });

        if (carrito.length === 0) {
            alert('El carrito está vacío');
            return;
        }

        // Enviar al nuevo endpoint de procesamiento de compra
        fetch('../php/procesar_compra.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ carrito: carrito })
        }).then(r => r.json()).then(data => {
            if (data.ok) {
                alert(data.msg + '\nPedido #: ' + data.id_pedido + '\nTotal: $' + parseFloat(data.total).toFixed(2));

                const totalEl = document.getElementById('total');
                if (totalEl) totalEl.textContent = '$0';

                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');

                sincronizarStorage();

                // Recargar la página para actualizar los stocks
                setTimeout(() => location.reload(), 1500);
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

                const cartBody = document.getElementById('lista-libro'); // Corregido cuando queramos vaciar el carrito
                if (cartBody) cartBody.innerHTML = '';

                const carritoAcciones = document.getElementById('carrito-acciones');
                if (carritoAcciones) carritoAcciones.classList.add('disabled');

                sincronizarStorage(); // Borramos memoria
            }
        }).catch(err => { console.error(err); });
    }
});

// LOGICA DEL BOTÓN CARGAR MÁS OFERTAS
document.addEventListener('DOMContentLoaded', () => {

    let currentItem = 4; // Cuántos productos se muestran al inicio
    const cajas = document.querySelectorAll('.box');
    const btnLoadMore = document.getElementById('load-more');
    const noResultMsg = document.getElementById('no-results');

    // Función que controla cuántos productos se ven (Para el botón Cargar Más)
    function actualizarVistaCargarMas() {
        for (let i = 0; i < cajas.length; i++) {
            if (i < currentItem) {
                cajas[i].style.display = '';
            } else {
                cajas[i].style.display = 'none';
            }
        }
        // Mostrar u ocultar el botón si ya no hay más productos
        if (btnLoadMore) {
            btnLoadMore.style.display = (currentItem >= cajas.length) ? 'none' : 'inline-block';
        }
    }

    // 1. Ejecutar al cargar la página
    actualizarVistaCargarMas();

    // 2. Darle vida al botón "Cargar más"
    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function () {
            currentItem += 4;
            actualizarVistaCargarMas();
        });
    }

    // 3. Darle vida al Buscador
    const searchInput = document.getElementById('search-input');
    const offcanvasSearchInput = document.getElementById('search-input-mobile');

    function filtrarProductos(termino) {
        const term = termino.toLowerCase().trim();
        let resul = false;

        // Si el buscador se queda vacío, regresamos al estado de "Cargar más"
        if (term === '') {
            actualizarVistaCargarMas();
            if (noResultMsg) noResultMsg.style.display = 'none';
            return;
        }

        // Si el usuario escribe algo, ocultamos el botón de cargar más temporalmente
        if (btnLoadMore) btnLoadMore.style.display = 'none';

        // Filtramos buscando coincidencias
        cajas.forEach(caja => {
            const elementoP = caja.querySelector('.product-name');

            if (elementoP) {
                const nombreP = elementoP.textContent.toLowerCase();
                if (nombreP.includes(term)) {
                    caja.style.display = '';
                    resul = true;
                } else {
                    caja.style.display = 'none';
                }
            }
        });

        // Mostrar el mensaje de "No se encontró" si no hay resultados
        if (resul) {
            if (noResultMsg) noResultMsg.style.display = 'none';
        } else {
            if (noResultMsg) noResultMsg.style.display = 'block';
        }
    }

    // Activar el filtro al escribir en los buscadores PC y Móvil
    if (searchInput) {
        searchInput.addEventListener('input', (e) => filtrarProductos(e.target.value));
    }
    if (offcanvasSearchInput) {
        offcanvasSearchInput.addEventListener('input', (e) => filtrarProductos(e.target.value));
    }

    // Lógica de los botones verdes de "Buscar" (solo para hacer scroll)
    const botonB = document.querySelectorAll('form[role="search"] .btn-success');
    botonB.forEach(boton => {
        boton.addEventListener('click', () => {
            if (searchInput) searchInput.blur();
            if (offcanvasSearchInput) offcanvasSearchInput.blur();

            const destinoScroll = document.querySelector('.products') || document.getElementById('product-container');
            if (destinoScroll) {
                destinoScroll.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
function confirmarBorrado(id_producto) {
    let respuesta = confirm("¿Seguro que deseas Borrar?");
    if (respuesta === true) {
        window.location.href = "../php/eliminar.php?id=" + id_producto;
    } else {
        console.log("Decidio no borrar");
    }
}
document.addEventListener('DOMContentLoaded', function () {
    const botonesEditar = document.querySelectorAll('.btn-editar');

    if (botonesEditar.length > 0) {
        const modalEditarUnico = new bootstrap.Modal(document.getElementById('modalEditarUnico'));

        botonesEditar.forEach(boton => {
            boton.addEventListener('click', function () {
                // Llenamos el formulario con los datos del botón
                document.getElementById('edit_id_producto').value = this.getAttribute('data-id');
                document.getElementById('edit_nombre').value = this.getAttribute('data-nombre');
                document.getElementById('edit_serie').value = this.getAttribute('data-serie');
                document.getElementById('edit_fecha').value = this.getAttribute('data-fecha');
                document.getElementById('edit_unidades').value = this.getAttribute('data-unidades');
                document.getElementById('edit_precio').value = this.getAttribute('data-precio');
                document.getElementById('edit_categoria').value = this.getAttribute('data-categoria');

                // Abrimos el modal
                modalEditarUnico.show();
            });
        });
    }
});

