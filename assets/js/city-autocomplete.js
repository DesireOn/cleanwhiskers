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

    const service = window.cwMobileService || 'mobile-dog-grooming';

    const options = Array.from(listEl.querySelectorAll('.city-suggestion')).map((opt) => ({
        value: opt.dataset.value,
        label: opt.textContent,
    }));

    document.body.appendChild(listEl);
    listEl.innerHTML = '';
    listEl.hidden = true;
    listEl.setAttribute('aria-live', 'polite');
    if (!listEl.getAttribute('aria-label')) {
        listEl.setAttribute('aria-label', 'City suggestions');
    }

    let activeIndex = -1;
    let currentInput = null;
    let navigating = false;
    // Track pointer interactions on the list to avoid blur/click race
    let pointerDownOnList = false;

    const hide = () => {
        listEl.hidden = true;
        if (currentInput) {
            currentInput.setAttribute('aria-expanded', 'false');
            currentInput.removeAttribute('aria-activedescendant');
        }
        activeIndex = -1;
    };

    const positionList = (input) => {
        const rect = input.getBoundingClientRect();
        const viewportHeight = Math.max(window.innerHeight || 0, document.documentElement.clientHeight || 0);
        const spaceBelow = viewportHeight - rect.bottom;
        const spaceAbove = rect.top;
        const verticalGap = 8;

        let maxHeight = Math.min(360, spaceBelow - verticalGap);
        let top = rect.bottom + window.scrollY + verticalGap;
        let openUpwards = false;

        if (maxHeight < 180 && spaceAbove > spaceBelow) {
            maxHeight = Math.min(360, spaceAbove - verticalGap);
            top = rect.top + window.scrollY - maxHeight - verticalGap;
            openUpwards = true;
        }

        if (maxHeight < 180) {
            maxHeight = Math.min(360, viewportHeight - verticalGap * 2);
            if (maxHeight < 120) {
                maxHeight = 120;
            }
            top = Math.max(verticalGap, Math.min(top, viewportHeight - maxHeight - verticalGap + window.scrollY));
        }

        listEl.classList.toggle('city-suggestions--reverse', openUpwards);
        listEl.style.position = 'absolute';
        listEl.style.left = `${rect.left + window.scrollX}px`;
        listEl.style.top = `${top}px`;
        listEl.style.width = `${rect.width}px`;
        listEl.style.maxHeight = `${maxHeight}px`;
    };

    const navigate = (option, card) => {
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
                currentInput.value = option.label || option.value;
            }
            window.location.href = `/groomers/${option.value}/${service}`;
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
            card.href = `/groomers/${opt.value}/${service}`;

            const icon = document.createElement('span');
            icon.className = 'city-card__icon';
            icon.setAttribute('aria-hidden', 'true');
            card.appendChild(icon);

            const label = document.createElement('span');
            label.className = 'city-card__label';
            label.innerHTML = regex ? opt.label.replace(regex, '<mark>$1</mark>') : opt.label;
            card.appendChild(label);

            const meta = document.createElement('span');
            meta.className = 'city-card__meta';
            meta.textContent = 'Tap to see available groomers';
            card.appendChild(meta);

            const handleNavigation = (e) => {
                e.preventDefault();
                navigate(opt, card);
            };
            // Fallback for keyboard activation on the option itself
            card.addEventListener('click', handleNavigation);
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    handleNavigation(e);
                }
            });

            listEl.appendChild(card);
        });
        if (!matches.length) {
            const emptyState = document.createElement('div');
            emptyState.className = 'city-card city-card--empty';
            emptyState.setAttribute('role', 'status');

            const emptyLabel = document.createElement('span');
            emptyLabel.className = 'city-card__label';
            emptyLabel.textContent = 'No matching cities yet';
            emptyState.appendChild(emptyLabel);

            const suggestion = document.createElement('span');
            suggestion.className = 'city-card__meta';
            suggestion.textContent = 'Try a nearby city or check your spelling.';
            emptyState.appendChild(suggestion);

            listEl.appendChild(emptyState);
        }

        if (listEl.childElementCount) {
            positionList(input);
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

    const defaultSuggestions = () => options.slice(0, Math.min(5, options.length));

    // Track pointer state on the whole list to mitigate blur races
    listEl.addEventListener('pointerdown', () => {
        pointerDownOnList = true;
    });
    listEl.addEventListener('pointerup', () => {
        pointerDownOnList = false;
    });
    listEl.addEventListener('pointercancel', () => {
        pointerDownOnList = false;
    });

    const repositionIfOpen = () => {
        if (!listEl.hidden && currentInput) {
            positionList(currentInput);
        }
    };

    window.addEventListener('resize', repositionIfOpen);
    window.addEventListener('scroll', repositionIfOpen, true);

    inputs.forEach((input) => {
        input.addEventListener('input', () => onInput(input));
        input.addEventListener('focus', () => {
            // Zero-keystroke: show a few suggestions immediately on focus
            if (listEl.hidden) {
                render(input, defaultSuggestions(), '');
            }
        });
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
                    const option = options.find((opt) => opt.value === item.dataset.value);
                    if (option) {
                        navigate(option, item);
                    }
                }
            } else if (e.key === 'Escape') {
                hide();
            }
        });
        input.addEventListener('blur', () => {
            // If the blur is caused by interacting with the list, don't hide immediately
            // to avoid swallowing the selection click.
            if (pointerDownOnList) {
                setTimeout(() => {
                    if (!pointerDownOnList) hide();
                }, 150);
            } else {
                setTimeout(hide, 100);
            }
        });
    });
}
