document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('search-form');
    const city = document.getElementById('city');
    const submit = document.getElementById('search-submit');
    if (!form || !submit) {
        return;
    }
    if (city) {
        submit.disabled = city.value.trim() === '';
        city.addEventListener('input', () => {
            submit.disabled = city.value.trim() === '';
        });
    }
    form.addEventListener('submit', () => {
        if (submit.disabled) {
            return;
        }
        submit.setAttribute('aria-busy', 'true');
        submit.disabled = true;
        let existing = null;
        if (typeof submit.querySelector === 'function') {
            existing = submit.querySelector('.spinner');
        } else if (submit.children) {
            for (let i = 0; i < submit.children.length; i++) {
                if (submit.children[i].className === 'spinner') {
                    existing = submit.children[i];
                    break;
                }
            }
        }
        if (!existing) {
            const spinner = document.createElement('span');
            spinner.className = 'spinner';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-live', 'polite');
            const hidden = document.createElement('span');
            hidden.className = 'visually-hidden';
            hidden.textContent = 'Loading';
            spinner.appendChild(hidden);
            submit.insertBefore(spinner, submit.firstChild);
        }
    });
});
