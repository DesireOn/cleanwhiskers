function debounce(fn, delay = 200) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function escapeRegExp(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export default function initCityAutocomplete(inputsParam) {
    const inputs = Array.from(inputsParam || []);
    const listbox = document.getElementById('city-list');
    if (!inputs.length || !listbox) {
        return;
    }

    const options = Array.from(listbox.querySelectorAll('.city-suggestion')).map((opt) => ({
        value: opt.dataset.value,
        label: opt.textContent,
        icon: opt.dataset.icon,
        url: opt.dataset.url,
    }));

    document.body.appendChild(listbox);
    listbox.innerHTML = '';
    listbox.hidden = true;

    const hide = () => {
        listbox.hidden = true;
        inputs.forEach((i) => {
            i.setAttribute('aria-expanded', 'false');
            i.removeAttribute('aria-activedescendant');
        });
    };

    const render = (input, matches) => {
        listbox.innerHTML = '';
        matches.forEach((opt) => {
            const card = document.createElement('div');
            card.className = 'city-card';
            card.setAttribute('role', 'option');
            card.setAttribute('tabindex', '0');
            card.dataset.value = opt.value;
            card.setAttribute('aria-selected', 'false');

            const icon = document.createElement('span');
            icon.className = 'city-card__icon';
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = opt.icon || (opt.label?.[0] ?? '🏙️');

            const name = document.createElement('span');
            name.className = 'city-card__name';
            name.textContent = opt.label;

            card.append(icon, name);
            listbox.appendChild(card);

            const select = () => {
                input.value = opt.value;
                card.setAttribute('aria-selected', 'true');
                hide();

                const url = opt.url || (typeof routes !== 'undefined' && routes.cityShow ? routes.cityShow.replace(':slug', opt.value) : null);
                if (url) window.location.assign(url);
            };

            card.addEventListener('click', select);
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    select();
                }
            });
        });

        if (matches.length) {
            const rect = input.getBoundingClientRect();
            listbox.style.position = 'absolute';
            listbox.style.left = `${rect.left + window.scrollX}px`;
            listbox.style.top = `${rect.bottom + window.scrollY}px`;
            listbox.style.width = `${rect.width}px`;
            listbox.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        } else {
            hide();
        }
    };

    const filterOptions = (val) =>
        options.filter((o) => o.label.toLowerCase().includes(val) || o.value.toLowerCase().includes(val));

    const onInput = debounce((input) => {
        const val = input.value.trim().toLowerCase();
        render(input, filterOptions(val));
    }, 200);

    inputs.forEach((input) => {
        input.addEventListener('input', () => onInput(input));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hide();
            }
        });
        input.addEventListener('blur', () => {
            setTimeout(hide, 100);
        });
    });
}
