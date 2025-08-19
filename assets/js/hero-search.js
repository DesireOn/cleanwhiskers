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

    const cityInput = document.getElementById('city');
    const dataList = document.getElementById('city-list');
    if (cityInput && dataList) {
        const options = Array.from(dataList.options);
        cityInput.addEventListener('input', () => {
            const val = cityInput.value.toLowerCase();
            dataList.innerHTML = '';
            options
                .filter((opt) => opt.textContent.toLowerCase().includes(val) || opt.value.toLowerCase().includes(val))
                .forEach((opt) => dataList.appendChild(opt));
        });
    }
});

