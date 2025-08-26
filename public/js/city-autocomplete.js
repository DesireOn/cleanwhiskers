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
    let navigating = false;

    const hide = () => {
        listEl.hidden = true;
        if (currentInput) {
            currentInput.setAttribute('aria-expanded', 'false');
            currentInput.removeAttribute('aria-activedescendant');
        }
        activeIndex = -1;
    };

    const navigate = (slug, card) => {
        if (navigating) {
            return;
        }
        navigating = true;
        listEl.setAttribute('aria-busy', 'true');
        let spinner = card.querySelector('.spinner');
        if (!spinner) {
            spinner = document.createElement('span');
            spinner.className = 'spinner';
            spinner.setAttribute('role', 'status');
            spinner.setAttribute('aria-live', 'polite');
            const hidden = document.createElement('span');
            hidden.className = 'visually-hidden';
            hidden.textContent = 'Loading';
            spinner.appendChild(hidden);
            card.appendChild(spinner);
        }

        const cleanup = () => {
            spinner.remove();
            listEl.removeAttribute('aria-busy');
            navigating = false;
        };

        try {
            if (currentInput) {
                currentInput.value = slug;
            }
            window.location.href = `/cities/${slug}`;
        } catch (err) {
            cleanup();
            throw err;
        }

        setTimeout(() => {
            if (!document.hidden) {
                cleanup();
            }
        }, 1000);
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
                item.setAttribute('aria-selected', 'true');
                currentInput.setAttribute('aria-activedescendant', item.id);
            } else {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            }
        });
    };

    const render = (input, matches, term) => {
        listEl.innerHTML = '';
        activeIndex = -1;
        const regex = term ? new RegExp(`(${escapeRegExp(term)})`, 'i') : null;
        matches.forEach((opt, index) => {
            const card = document.createElement('a');
            card.setAttribute('role', 'option');
            card.setAttribute('tabindex', '-1');
            card.setAttribute('aria-selected', 'false');
            card.id = `${input.id}-option-${index}`;
            card.className = 'city-card';
            card.dataset.value = opt.value;
            card.href = `/cities/${opt.value}`;

            const label = document.createElement('span');
            label.className = 'city-card__label';
            label.innerHTML = regex ? opt.label.replace(regex, '<mark>$1</mark>') : opt.label;
            card.appendChild(label);

            const handleNavigation = (e) => {
                e.preventDefault();
                navigate(opt.value, card);
            };
            card.addEventListener('click', handleNavigation);
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    handleNavigation(e);
                }
            });

            listEl.appendChild(card);
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
                    navigate(item.dataset.value, item);
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
