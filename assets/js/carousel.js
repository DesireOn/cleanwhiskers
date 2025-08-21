const carousels = document.querySelectorAll('[data-carousel]');

carousels.forEach((carousel) => {
    const track = carousel.querySelector('.carousel__track');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const cards = track.querySelectorAll('.carousel__card');

    if (!track || !prev || !next || !cards.length) {
        return;
    }

    track.setAttribute('tabindex', '0');

    let cardWidth = 0;

    function updateMetrics() {
        const style = getComputedStyle(track);
        const gap = parseInt(style.columnGap || style.gap, 10) || 0;
        cardWidth = cards[0].getBoundingClientRect().width + gap;
    }

    updateMetrics();
    window.addEventListener('resize', updateMetrics);

    function scrollByCard(direction) {
        const maxScroll = track.scrollWidth - track.clientWidth;
        let target = track.scrollLeft + direction * cardWidth;
        if (target < 0) {
            target = 0;
        }
        if (target > maxScroll) {
            target = maxScroll;
        }
        track.scrollTo({ left: target, behavior: 'smooth' });
    }

    prev.addEventListener('click', () => {
        scrollByCard(-1);
    });

    next.addEventListener('click', () => {
        scrollByCard(1);
    });

    let isDragging = false;
    let startX = 0;
    let scrollStart = 0;

    track.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse') {
            return;
        }
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

    function endDrag(e) {
        if (track.hasPointerCapture(e.pointerId)) {
            track.releasePointerCapture(e.pointerId);
        }
        isDragging = false;
    }

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('pointerleave', () => {
        isDragging = false;
    });

    track.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            scrollByCard(-1);
        } else if (event.key === 'ArrowRight') {
            scrollByCard(1);
        }
    });
});
