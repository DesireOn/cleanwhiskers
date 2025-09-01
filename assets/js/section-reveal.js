const initSectionReveal = () => {
    const targets = document.querySelectorAll('.reveal-on-scroll');
    if (!targets.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        targets.forEach((el) => el.classList.add('reveal-in'));
        return;
    }

    // Fallback for browsers without IntersectionObserver support
    if (typeof window.IntersectionObserver !== 'function') {
        targets.forEach((el) => el.classList.add('reveal-in'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-in');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    targets.forEach((el) => observer.observe(el));
};

document.addEventListener('DOMContentLoaded', initSectionReveal);
