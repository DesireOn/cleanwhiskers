document.addEventListener('DOMContentLoaded', () => {
    const city = document.getElementById('city');
    const submit = document.getElementById('search-submit');
    if (city && submit) {
        submit.disabled = city.value.trim() === '';
        city.addEventListener('input', () => {
            submit.disabled = city.value.trim() === '';
        });
    }

});
