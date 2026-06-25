document.addEventListener("DOMContentLoaded", function () {

    // EFECTOS VISUALES Y ALERTAS GLOBALES
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm('¿Estas seguro que desea realizar esta accion?')) {
                e.preventDefault();
            }
        });
    });

    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            if (input.parentElement) {
                input.parentElement.style.transition = '0.3s';
            }
        });
    });

    // LÓGICA DEL LOGIN DE ADMIN
    const formLogin = document.getElementById('form-login');
    const inputCorreo = document.getElementById('correo');

    if (formLogin) {
        formLogin.addEventListener('submit', function (e) {
            const correo = document.getElementById('correo').value.trim();
            const contrasena = document.getElementById('contrasena').value.trim();
            const codigo = document.getElementById('codigo_admin').value.trim();

            if (!correo || !contrasena || !codigo) {
                e.preventDefault();
                alert('Por favor completa todos los campos del login');
            }
        });
    }

    if (inputCorreo) {
        inputCorreo.addEventListener('blur', function () {
            if (this.value.trim()) {
                this.classList.remove('error');
                this.classList.add('success');
            }
        });
    }

    // LÓGICA DEL REGISTRO DE ADMIN
    const formRegistro = document.getElementById('form-registro');
    
    if (formRegistro) {
        formRegistro.addEventListener('submit', function (e) {
            const usuario = document.getElementById('usuario').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const contrasena = document.getElementById('contrasena').value;
            const confirm_contrasena = document.getElementById('confirm_contrasena').value;
            const codigo = document.getElementById('codigo_admin').value.trim();

            if (!usuario || !correo || !contrasena || !confirm_contrasena || !codigo) {
                e.preventDefault();
                alert('Por favor completa todos los campos del registro');
                return;
            }

            if (contrasena.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres');
                return;
            }

            if (contrasena !== confirm_contrasena) {
                e.preventDefault();
                document.getElementById('confirm_contrasena').style.borderColor = '#dc3545';
                alert('Las contraseñas no coinciden');
                return;
            }
        });

        // Efecto visual para confirmar contraseña
        const passRegistro = document.getElementById('contrasena');
        const confirmRegistro = document.getElementById('confirm_contrasena');

        if (passRegistro && confirmRegistro) {
            confirmRegistro.addEventListener('keyup', function () {
                if (this.value === passRegistro.value) {
                    this.style.borderColor = '#28a745'; // Verde si coinciden
                } else if (this.value) {
                    this.style.borderColor = '#dc3545'; // Rojo si no coinciden
                } else {
                    this.style.borderColor = '#ccc'; // Gris si está vacío
                }
            });
        }
    }

    // LÓGICA DEL BOTÓN DE CAMBIO DE CONTRASEÑA
    const passBtn = document.getElementById('pass');
    
    if (passBtn) {
        passBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('¿Deseas Cambiar tu Contraseña?')) {
                window.location.href = '../php/cambiar_contraseña.php';
            }
        });
    }

    // MEDIDOR DE TIEMPO ACTIVO EN SESIÓN (GAUGE)
 
    (function () {
        // gaugeLoginTime es inyectado por PHP en perfiladmin.php
        // antes de cargar este script
        if (typeof window.gaugeLoginTime === 'undefined') return;

        var loginTime = window.gaugeLoginTime;
        var MAX_MS    = 8 * 60 * 60 * 1000; // Escala máxima: 8 horas
        var ARC_LEN   = 251;                  // Longitud total del arco SVG

        var arc    = document.getElementById('gauge-arc');
        var needle = document.getElementById('gauge-needle');
        var pivot  = document.getElementById('gauge-pivot');
        var timeEl = document.getElementById('gauge-time');
        var badge  = document.getElementById('gauge-badge');
        var label  = document.getElementById('gauge-label');

        // Si los elementos del gauge no existen en esta página, salir
        if (!arc || !needle || !timeEl) return;

        // Interpola entre dos valores
        function lerp(a, b, t) {
            return a + (b - a) * t;
        }

        // Devuelve un color RGB que va de verde, amarillo, rojo
        function getColor(pct) {
            var r, g, b;
            if (pct < 0.5) {
                var t = pct / 0.5;
                r = Math.round(lerp(34,  234, t));
                g = Math.round(lerp(197, 179, t));
                b = Math.round(lerp(94,  8,   t));
            } else {
                var t = (pct - 0.5) / 0.5;
                r = Math.round(lerp(234, 239, t));
                g = Math.round(lerp(179, 68,  t));
                b = Math.round(lerp(8,   68,  t));
            }
            return 'rgb(' + r + ',' + g + ',' + b + ')';
        }

        // Formatea milisegundos a HH:MM:SS
        function formatTime(ms) {
            var totalSeg = Math.floor(ms / 1000);
            var h = Math.floor(totalSeg / 3600);
            var m = Math.floor((totalSeg % 3600) / 60);
            var s = totalSeg % 60;
            return String(h).padStart(2, '0') + ':' +
                   String(m).padStart(2, '0') + ':' +
                   String(s).padStart(2, '0');
        }

        function updateGauge() {
            var elapsed = Date.now() - loginTime;
            var pct     = Math.min(elapsed / MAX_MS, 1);
            var color   = getColor(pct);

            // Animación del arco de progreso
            arc.setAttribute('stroke-dashoffset', ARC_LEN - pct * ARC_LEN);
            arc.setAttribute('stroke', color);

            // Animación de la aguja: -90° y +90°
            var angleDeg = -90 + pct * 180;
            needle.setAttribute('transform', 'rotate(' + angleDeg + ',90,90)');
            needle.setAttribute('stroke', color);
            pivot.setAttribute('fill', color);

            // Tiempo digital
            timeEl.textContent = formatTime(elapsed);
            timeEl.style.color = color;

            // Badge de estado y etiqueta
            badge.classList.remove('gauge-badge--bajo', 'gauge-badge--medio', 'gauge-badge--alto');

            if (pct < 0.25) {
                badge.textContent = 'Bajo';
                badge.classList.add('gauge-badge--bajo');
                label.textContent = 'Sesión reciente';
            } else if (pct < 0.65) {
                badge.textContent = 'Medio';
                badge.classList.add('gauge-badge--medio');
                label.textContent = 'Tiempo moderado';
            } else {
                badge.textContent = 'Alto';
                badge.classList.add('gauge-badge--alto');
                label.textContent = 'Sesión prolongada';
            }
        }

        // Actualizar cada segundo
        setInterval(updateGauge, 1000);
        updateGauge();
    })();

    const toggleSwitch = document.getElementById('toggle');
    
    // 2. Buscamos en la memoria del navegador si el usuario ya tenía el modo oscuro
    const currentTheme = localStorage.getItem('tema_LM');

    // 3. Si la memoria dice "oscuro", pintamos la página de oscuro al cargar
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        // Si el interruptor existe en esta página, lo marcamos como encendido
        if (toggleSwitch) {
            toggleSwitch.checked = true;
        }
    }

    // 4. Escuchar cuando el usuario hace clic en el interruptor
    if (toggleSwitch) {
        toggleSwitch.addEventListener('change', function () {
            if (this.checked) {
                // Encendido: Aplicamos la clase y guardamos en memoria
                document.body.classList.add('dark-mode');
                localStorage.setItem('tema_LM', 'dark');
            } else {
                // Apagado: Quitamos la clase y guardamos en memoria
                document.body.classList.remove('dark-mode');
                localStorage.setItem('tema_LM', 'light');
            }
        });
    }

});