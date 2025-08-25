// assets/js/mobile-cta.js
import { setLoading } from './loading.js';

document.addEventListener('DOMContentLoaded', () => {
  const cta = document.getElementById('mobile-cta');
  if (!cta) return;

  // Show only on mobile (same as CSS guard; keeps SSR hidden to avoid FOUC)
  const isMobile = () => window.matchMedia('(max-width: 767px)').matches;
  const btn = cta.querySelector('.mobile-cta__btn');
  const target = cta.getAttribute('data-target-route');

  const toggleVisibility = () => {
    if (isMobile()) {
      cta.hidden = false;
      cta.classList.add('mobile-cta--visible');
      // Hide/disable back-to-top on mobile to avoid overlap
      const backToTop = document.querySelector('.back-to-top');
      if (backToTop) backToTop.style.display = 'none';
    } else {
      cta.hidden = true;
      const backToTop = document.querySelector('.back-to-top');
      if (backToTop) backToTop.style.display = '';
    }
  };

  toggleVisibility();
  window.addEventListener('resize', toggleVisibility);

  btn.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!target) return;
    setLoading(btn, true);
    try {
      // Lightweight preflight same as cta-button.js pattern
      await fetch(target, { method: 'HEAD' });
      window.location.assign(target);
    } catch (err) {
      setLoading(btn, false);
      // optional: toast/error UI
    }
  });
});
