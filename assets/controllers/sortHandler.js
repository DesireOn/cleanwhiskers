const initSortHandler = () => {
    const select = document.querySelector('.sort-dropdown__select');
    if (!select) {
        return;
    }

    select.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('sort', select.value);
        window.location.search = params.toString();
    });
};

window.addEventListener('DOMContentLoaded', initSortHandler);
