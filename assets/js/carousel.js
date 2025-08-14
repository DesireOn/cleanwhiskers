const carousels = document.querySelectorAll('[data-carousel]');

carousels.forEach((carousel) => {
    const track = carousel.querySelector('.carousel__track');
    const prev = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const cards = track.querySelectorAll('.carousel__card');

    if (!cards.length) {
        return;
    }

    const cardWidth = cards[0].getBoundingClientRect().width;

    function scrollBy(offset) {
        track.scrollBy({ left: offset, behavior: 'smooth' });
    }

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
