document.addEventListener('DOMContentLoaded', () => {
    if (window.matchMedia('(max-width: 768px)').matches) {
        return;
    }
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

    const threshold = 200;
    let lastScrollY = window.scrollY;

    const handleScroll = () => {
        const currentY = window.scrollY;

        if (currentY > threshold && currentY > lastScrollY) {
            sticky.classList.add('search--compact');
        } else if (currentY < lastScrollY || currentY <= threshold) {
            sticky.classList.remove('search--compact');
        }

        lastScrollY = currentY;
        ticking = false;
    };

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(handleScroll);
            ticking = true;
        }
    });
});
