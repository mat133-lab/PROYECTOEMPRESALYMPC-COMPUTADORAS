document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        window.setTimeout(function () {
            alertBox.classList.add('fade');
            alertBox.addEventListener('transitionend', function () {
                alertBox.remove();
            });
        }, 6000);
    }
});
