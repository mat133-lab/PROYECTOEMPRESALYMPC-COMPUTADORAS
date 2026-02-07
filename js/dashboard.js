// Script para manejar carrito de compras en dashboard
document.addEventListener('click', function(e){
    
    // Agregar producto al carrito
    if(e.target.matches('.agregar-libro')){
        e.preventDefault();
        const id = e.target.dataset.id;
        
        fetch('../php/cart_action.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({product_id:id, qty:1, action:'add'})
        }).then(r=>r.json()).then(data=>{
            if(data.ok){
                // Actualizar cantidad visible en el producto
                const el = document.querySelector('.product-units[data-id="'+id+'"]');
                if(el) el.textContent = data.stock;
                
                // Actualizar total del carrito
                const totalEl = document.getElementById('total');
                if(totalEl){
                    let current = parseFloat(totalEl.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                    const priceEl = e.target.closest('.product-txt').querySelector('.precio');
                    let price = 0;
                    if(priceEl){ price = parseFloat(priceEl.textContent.replace(/[^0-9.-]+/g, '')) || 0; }
                    current = current + price;
                    totalEl.textContent = '$' + current.toFixed(2);
                }
                
                // Habilitar botones del carrito
                const carritoAcciones = document.getElementById('carrito-acciones');
                if(carritoAcciones) carritoAcciones.classList.remove('disabled');
                
                alert(data.msg);
            } else { 
                alert(data.msg); 
            }
        }).catch(err=>{ 
            alert('Error en la solicitud'); 
            console.error(err); 
        });
    }

    // Procesar compra
    if(e.target.id === 'carrito-acciones-comprar'){
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'purchase'})
        }).then(r=>r.json()).then(data=>{
            if(data.ok){
                alert(data.msg);
                const totalEl = document.getElementById('total'); 
                if(totalEl) totalEl.textContent = '$0';
                
                const carritoAcciones = document.getElementById('carrito-acciones');
                if(carritoAcciones) carritoAcciones.classList.add('disabled');
            } else { 
                alert(data.msg); 
            }
        }).catch(err=>{ 
            alert('Error en la compra'); 
            console.error(err); 
        });
    }

    // Vaciar carrito
    if(e.target.id === 'carrito-acciones-vaciar'){
        e.preventDefault();
        fetch('../php/cart_action.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'clear'})
        }).then(r=>r.json()).then(data=>{
            if(data.ok){
                const totalEl = document.getElementById('total'); 
                if(totalEl) totalEl.textContent = '$0';
                
                const carritoAcciones = document.getElementById('carrito-acciones');
                if(carritoAcciones) carritoAcciones.classList.add('disabled');
                
                alert(data.msg);
            }
        }).catch(err=>{ console.error(err); });
    }
});
