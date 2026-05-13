document.addEventListener('DOMContentLoaded', () => {
    const modalCloseButtons = document.querySelectorAll('[data-dialog-close]');
    modalCloseButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const dialog = button.closest('dialog');
            if (dialog) {
                dialog.close();
            }
        });
    });
});
