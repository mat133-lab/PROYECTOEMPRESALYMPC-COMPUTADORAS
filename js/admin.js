
// Validación del formulario
document.querySelector('form').addEventListener('submit', function (e) {
    const correo = document.getElementById('correo').value.trim();
    const contrasena = document.getElementById('contrasena').value.trim();
    const codigo = document.getElementById('codigo_admin').value.trim();

    if (!correo || !contrasena || !codigo) {
        e.preventDefault();
        alert('Por favor completa todos los campos');
    }
});

// Validación en tiempo real
document.getElementById('correo').addEventListener('blur', function () {
    if (this.value.trim()) {
        this.classList.remove('error');
        this.classList.add('success');
    }
});
// Validación del formulario
document.querySelector('form').addEventListener('submit', function (e) {
    const usuario = document.getElementById('usuario').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const contrasena = document.getElementById('contrasena').value;
    const confirm_contrasena = document.getElementById('confirm_contrasena').value;
    const codigo = document.getElementById('codigo_admin').value.trim();

    if (!usuario || !correo || !contrasena || !confirm_contrasena || !codigo) {
        e.preventDefault();
        alert('Por favor completa todos los campos');
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

// Validación en tiempo real de contraseñas
const contrasena = document.getElementById('contrasena');
const confirm_contrasena = document.getElementById('confirm_contrasena');

confirm_contrasena.addEventListener('keyup', function () {
    if (this.value === contrasena.value) {
        this.style.borderColor = '#28a745';
    } else if (this.value) {
        this.style.borderColor = '#dc3545';
    } else {
        this.style.borderColor = '#e9e9e9';
    }
});
