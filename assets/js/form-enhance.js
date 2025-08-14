// Progressive enhancement for form validation messages

document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
        const fields = form.querySelectorAll('input[required], select[required]');
        fields.forEach((field) => {
            const errorId = field.getAttribute('aria-describedby');
            const errorEl = errorId ? document.getElementById(errorId) : null;

            field.addEventListener('invalid', (e) => {
                e.preventDefault();
                if (errorEl) {
                    errorEl.textContent = field.validationMessage;
                    field.setAttribute('aria-invalid', 'true');
                }
            });

            field.addEventListener('input', () => {
                if (errorEl) {
                    errorEl.textContent = '';
                    field.removeAttribute('aria-invalid');
                }
            });
        });
    });
});
