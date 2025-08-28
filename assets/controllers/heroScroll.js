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
      }
    });
  });
})();
