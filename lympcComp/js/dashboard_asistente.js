document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.box').forEach((box) => {
        box.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                box.click();
            }
        });
    });
});
