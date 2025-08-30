// Smooth-scroll handler for elements with [data-scroll-target]
import { qsa, on, smoothScrollTo } from '../helpers/dom.js';

(function initHeroScroll() {
  const triggers = qsa('[data-scroll-target]');
  if (!triggers.length) return;

  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  triggers.forEach((trigger) => {
    on(trigger, 'click', (e) => {
      const targetSel = trigger.getAttribute('data-scroll-target');
      if (!targetSel) return;
      const targetEl = document.querySelector(targetSel);
      if (!targetEl) return;

      e.preventDefault();

      if (prefersReduced) {
        // Respect reduced motion: jump-link fallback
        try {
          targetEl.setAttribute('tabindex', '-1');
          targetEl.focus({ preventScroll: true });
        } catch (_) {}
        const id = targetEl.id ? `#${targetEl.id}` : targetSel;
        if (id && id.startsWith('#')) {
          window.location.hash = id.substring(1);
        } else {
          targetEl.scrollIntoView();
        }
      } else {
        smoothScrollTo(targetEl);
        // If we're scrolling to the search form/city area, center and focus it nicely
        try {
          const hasCity = targetEl.matches('#search-form, #city') || !!targetEl.querySelector?.('.city-input');
          if (hasCity && typeof window.CW_focusCityAfterScroll === 'function') {
            window.CW_focusCityAfterScroll(targetEl);
          }
        } catch (_) {}
      }
    });
  });
})();
