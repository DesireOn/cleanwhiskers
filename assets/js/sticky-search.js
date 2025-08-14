document.addEventListener('DOMContentLoaded', () => {
    const sticky = document.getElementById('sticky-search');
    const hero = document.querySelector('.hero');
    if (!sticky || !hero) {
        return;
    }

    const observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            sticky.classList.remove('is-sticky');
        } else {
            sticky.classList.add('is-sticky');
        }
    });

    observer.observe(hero);
});
