document.addEventListener('DOMContentLoaded', () => {
    const btn = document.querySelector('.hero__cta');
    if (!btn) {
        return;
    }
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const selector = btn.getAttribute('data-scroll-target') || '#groomer-listings';
        const target = document.querySelector(selector);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
