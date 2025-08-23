document.addEventListener('DOMContentLoaded', () => {
    const serviceInput = document.getElementById('service');
    const serviceButtons = document.querySelectorAll('.hero__service');

    if (serviceInput && serviceButtons.length) {
        const resetButtons = () => {
            serviceButtons.forEach((b) => b.setAttribute('aria-pressed', 'false'));
        };

        const selectButton = (btn) => {
            resetButtons();
            btn.setAttribute('aria-pressed', 'true');
            serviceInput.value = btn.dataset.value || '';
        };

        serviceButtons.forEach((btn, index) => {
            btn.addEventListener('click', () => {
                const isSelected = btn.getAttribute('aria-pressed') === 'true';
                if (isSelected) {
                    resetButtons();
                    serviceInput.value = '';
                } else {
                    selectButton(btn);
                }
            });

            btn.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    btn.click();
                } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = (index + 1) % serviceButtons.length;
                    serviceButtons[next].focus();
                } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = (index - 1 + serviceButtons.length) % serviceButtons.length;
                    serviceButtons[prev].focus();
                }
            });
        });
    }

    const dataEl = document.getElementById('city-data');
    if (!dataEl) {
        return;
    }
    const cities = JSON.parse(dataEl.textContent);

    const setupCombobox = (input, list) => {
        let activeIndex = -1;

        const closeList = () => {
            list.innerHTML = '';
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        };

        const setActive = (option) => {
            list.querySelectorAll('[role="option"]').forEach((o) => {
                o.classList.remove('is-active');
                o.removeAttribute('aria-selected');
            });
            option.classList.add('is-active');
            option.setAttribute('aria-selected', 'true');
            input.setAttribute('aria-activedescendant', option.id);
        };

        const openList = (items) => {
            list.innerHTML = '';
            items.forEach((city, idx) => {
                const opt = document.createElement('div');
                opt.id = `${list.id}-option-${idx}`;
                opt.setAttribute('role', 'option');
                opt.textContent = city.name;
                opt.dataset.value = city.slug;
                opt.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    input.value = city.slug;
                    closeList();
                });
                list.appendChild(opt);
            });
            if (items.length) {
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
            } else {
                closeList();
            }
        };

        input.addEventListener('input', () => {
            const val = input.value.toLowerCase();
            const matches = cities.filter(
                (c) => c.name.toLowerCase().includes(val) || c.slug.toLowerCase().includes(val),
            );
            openList(matches);
        });

        input.addEventListener('keydown', (e) => {
            const options = list.querySelectorAll('[role="option"]');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (options.length) {
                    activeIndex = (activeIndex + 1) % options.length;
                    setActive(options[activeIndex]);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (options.length) {
                    activeIndex = (activeIndex - 1 + options.length) % options.length;
                    setActive(options[activeIndex]);
                }
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && options[activeIndex]) {
                    e.preventDefault();
                    input.value = options[activeIndex].dataset.value || '';
                    closeList();
                }
            } else if (e.key === 'Escape') {
                closeList();
            }
        });

        input.addEventListener('blur', () => {
            setTimeout(closeList, 100);
        });
    };

    [
        { input: 'city', list: 'city-suggestions' },
        { input: 'sticky-city', list: 'sticky-city-suggestions' },
        { input: 'footer-city', list: 'footer-city-suggestions' },
    ].forEach(({ input, list }) => {
        const inputEl = document.getElementById(input);
        const listEl = document.getElementById(list);
        if (inputEl && listEl) {
            setupCombobox(inputEl, listEl);
        }
    });
});

