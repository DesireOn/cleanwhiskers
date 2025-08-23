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

    const cityInputs = ['city', 'sticky-city', 'footer-city']
        .map((id) => document.getElementById(id))
        .filter(Boolean);
    const listEl = document.getElementById('city-list');
    if (cityInputs.length && listEl) {
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

        const render = (input, matches) => {
            listEl.innerHTML = '';
            activeIndex = -1;
            matches.forEach((opt, index) => {
                const div = document.createElement('div');
                div.setAttribute('role', 'option');
                div.id = `${input.id}-option-${index}`;
                div.className = 'city-option';
                div.textContent = opt.label;
                div.dataset.value = opt.value;
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

        cityInputs.forEach((input) => {
            input.addEventListener('input', () => {
                const val = input.value.toLowerCase();
                const matches = options.filter(
                    (o) => o.label.toLowerCase().includes(val) || o.value.toLowerCase().includes(val),
                );
                render(input, matches);
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (listEl.hidden) {
                        render(input, options);
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
});

