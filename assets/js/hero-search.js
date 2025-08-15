document.addEventListener('DOMContentLoaded', () => {
    const serviceInput = document.getElementById('service');
    const serviceButtons = document.querySelectorAll('.hero__service');

    serviceButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const isSelected = btn.getAttribute('aria-pressed') === 'true';
            serviceButtons.forEach((b) => b.setAttribute('aria-pressed', 'false'));
            if (!isSelected) {
                btn.setAttribute('aria-pressed', 'true');
                serviceInput.value = btn.dataset.value || '';
            } else {
                serviceInput.value = '';
            }
        });
    });

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

