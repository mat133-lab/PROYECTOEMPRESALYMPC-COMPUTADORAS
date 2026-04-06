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
            // Asegúrate de que tus inputs en el HTML tengan estos IDs
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


    //  LÓGICA DEL BOTÓN DE CAMBIO DE CONTRASEÑA
    const passBtn = document.getElementById('pass');
    
    if (passBtn) {
        passBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('¿Deseas Cambiar tu Contraseña?')) {
                window.location.href = '../php/cambiar_contraseña.php';
            }
        });
    }

});