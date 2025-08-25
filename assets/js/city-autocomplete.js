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
    const listEl = document.getElementById('city-list');
    if (!inputs.length || !listEl) {
        return;
    }

    const options = Array.from(listEl.querySelectorAll('.city-suggestion')).map((opt) => ({
        value: opt.dataset.value,
        label: opt.textContent,
    }));

    document.body.appendChild(listEl);
    listEl.innerHTML = '';
    listEl.hidden = true;

    let activeIndex = -1;
    let currentInput = null;

    const hide = () => {
        listEl.hidden = true;
        if (currentInput) {
            currentInput.setAttribute('aria-expanded', 'false');
            currentInput.removeAttribute('aria-activedescendant');
        }
        activeIndex = -1;
    };

    const select = (opt) => {
        if (currentInput) {
            currentInput.value = opt.value;
        }
        hide();
    };

    const move = (dir) => {
        const items = listEl.querySelectorAll('[role="option"]');
        if (!items.length) {
            return;
        }
        activeIndex = (activeIndex + dir + items.length) % items.length;
        items.forEach((item, idx) => {
            if (idx === activeIndex) {
                item.classList.add('active');
                currentInput.setAttribute('aria-activedescendant', item.id);
            } else {
                item.classList.remove('active');
            }
        });
    };

    const render = (input, matches, term) => {
        listEl.innerHTML = '';
        activeIndex = -1;
        const regex = term ? new RegExp(`(${escapeRegExp(term)})`, 'i') : null;
        matches.forEach((opt, index) => {
            const div = document.createElement('div');
            div.setAttribute('role', 'option');
            div.id = `${input.id}-option-${index}`;
            div.className = 'city-option';
            div.dataset.value = opt.value;
            div.innerHTML = regex ? opt.label.replace(regex, '<mark>$1</mark>') : opt.label;
            div.addEventListener('mousedown', (e) => {
                e.preventDefault();
                select(opt);
            });
            listEl.appendChild(div);
        });
        if (matches.length) {
            const rect = input.getBoundingClientRect();
            listEl.style.position = 'absolute';
            listEl.style.left = `${rect.left + window.scrollX}px`;
            listEl.style.top = `${rect.bottom + window.scrollY}px`;
            listEl.style.width = `${rect.width}px`;
            listEl.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            currentInput = input;
        } else {
            hide();
        }
    };

    const filterOptions = (val) =>
        options.filter(
            (o) => o.label.toLowerCase().includes(val) || o.value.toLowerCase().includes(val),
        );

    const onInput = debounce((input) => {
        const val = input.value.trim().toLowerCase();
        const matches = filterOptions(val);
        render(input, matches, val);
    }, 200);

    inputs.forEach((input) => {
        input.addEventListener('input', () => onInput(input));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (listEl.hidden) {
                    render(input, filterOptions(input.value.trim().toLowerCase()), input.value.trim().toLowerCase());
                }
                move(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                move(-1);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0) {
                    e.preventDefault();
                    const item = listEl.querySelectorAll('[role="option"]')[activeIndex];
                    select({ value: item.dataset.value, label: item.textContent });
                }
            } else if (e.key === 'Escape') {
                hide();
            }
        });
        input.addEventListener('blur', () => {
            setTimeout(hide, 100);
        });
    });
}
