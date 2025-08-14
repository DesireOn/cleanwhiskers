document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.search-form');
    forms.forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                const spinner = button.querySelector('.spinner');
                if (spinner) {
                    spinner.hidden = false;
                }
            }
        });
    });
});
