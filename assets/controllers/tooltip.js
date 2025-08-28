// Placeholder: very lightweight title-based tooltip enhancement
import { qsa, on } from '../helpers/dom.js';

(function initTooltips() {
  const els = qsa('[data-tooltip]');
  if (!els.length) return;
  els.forEach((el) => {
    on(el, 'mouseenter', () => {
      // Minimal: rely on native title if present; no custom DOM added to avoid layout shifts
      if (!el.getAttribute('title') && el.dataset.tooltip) {
        el.setAttribute('title', el.dataset.tooltip);
      }
    });
  });
})();

