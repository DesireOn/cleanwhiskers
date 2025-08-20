const carousels = document.querySelectorAll('[data-carousel]');

carousels.forEach((carousel) => {
    const track = carousel.querySelector('.carousel__track');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const cards = track.querySelectorAll('.carousel__card');

    track.setAttribute('tabindex', '0');

    if (!cards.length) {
        return;
    }

    const cardWidth = cards[0].getBoundingClientRect().width;

    function scrollBy(offset) {
        track.scrollBy({ left: offset, behavior: 'smooth' });
    }

    let isDragging = false;
    let startX;
    let scrollStart;
    let touchStartX;

    track.addEventListener('pointerdown', (e) => {
        isDragging = true;
        startX = e.clientX;
        scrollStart = track.scrollLeft;
        track.setPointerCapture(e.pointerId);
    });

    track.addEventListener('pointermove', (e) => {
        if (!isDragging) {
            return;
        }
        const dx = e.clientX - startX;
        track.scrollLeft = scrollStart - dx;
    });

    track.addEventListener('pointerup', () => {
        isDragging = false;
    });

    track.addEventListener('pointerleave', () => {
        isDragging = false;
    });

    track.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    });

    track.addEventListener('touchend', (e) => {
        const touchEndX = e.changedTouches[0].clientX;
        const dx = touchEndX - touchStartX;
        if (Math.abs(dx) > 50) {
            if (dx < 0) {
                next.click();
            } else {
                prev.click();
            }
        }
    });

    prev.addEventListener('click', () => {
        if (track.scrollLeft <= 0) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
            return;
        }
        scrollBy(-cardWidth);
    });

    next.addEventListener('click', () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        if (track.scrollLeft >= maxScroll) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
            return;
        }
        scrollBy(cardWidth);
    });

    track.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            prev.click();
        }
        if (event.key === 'ArrowRight') {
            next.click();
        }
    });
});
