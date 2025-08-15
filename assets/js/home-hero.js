document.addEventListener('DOMContentLoaded', () => {
    const city = document.getElementById('city');
    const submit = document.getElementById('search-submit');
    if (city && submit) {
        submit.disabled = city.value.trim() === '';
        city.addEventListener('input', () => {
            submit.disabled = city.value.trim() === '';
        });
    }

    const video = document.getElementById('hero-video');
    const toggle = document.getElementById('hero-video-toggle');
    if (video && toggle) {
        toggle.addEventListener('click', () => {
            if (video.paused) {
                video.play();
                toggle.textContent = 'Pause';
            } else {
                video.pause();
                toggle.textContent = 'Play';
            }
        });
    }
});
