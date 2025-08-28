// Placeholder: smoothly handle hero anchor scrolls when present
import { qs, on, smoothScrollTo } from '../helpers/dom.js';

(function initHeroScroll() {
  const anchor = qs('#hero');
  if (!anchor) return;
  const trigger = qs('[data-hero-scroll]');
  if (!trigger) return;
  on(trigger, 'click', (e) => {
    e.preventDefault();
    smoothScrollTo(anchor);
  });
})();

