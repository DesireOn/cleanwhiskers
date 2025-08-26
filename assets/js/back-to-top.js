// Handles visibility of the back-to-top button based on scroll position
// Hides the button initially and fades it in after scrolling 300px

const initBackToTop = () => {
    const btn = document.querySelector('.back-to-top');
    if (!btn) {
        return;
    }

    // Inject minimal styles to handle fade in/out
    const style = document.createElement('style');
    style.textContent = `
.back-to-top { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
.back-to-top.is-visible { opacity: 1; pointer-events: auto; }
`;
    document.head.appendChild(style);

    let timeoutId;

    const updateVisibility = () => {
        btn.classList.toggle('is-visible', window.scrollY > 300);
    };

    const onScroll = () => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(updateVisibility, 100);
    };

    updateVisibility();
    window.addEventListener('scroll', onScroll, { passive: true });
};

document.addEventListener('DOMContentLoaded', initBackToTop);
