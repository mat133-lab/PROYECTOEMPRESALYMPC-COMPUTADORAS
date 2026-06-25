const loginTime = Number(document.body.dataset.loginTime || 0) * 1000;
if (loginTime) {
    window.gaugeLoginTime = loginTime;
}

const passwordButton = document.getElementById('pass');
if (passwordButton) {
    passwordButton.addEventListener('click', () => {
        window.location.href = '../php/cambiar_contraseña.php';
    });
}
