// Accessible tooltip controller (inline tooltips, no focus trap)
import { qsa, on } from '../helpers/dom.js';

(function initTooltips() {
  const triggers = qsa('[data-tooltip][aria-describedby]');
  if (!triggers.length) return;

  let current = null; // { trigger, tip }
  let unbindDocHandlers = () => {};

  function getTip(trigger) {
    const id = trigger.getAttribute('aria-describedby');
    if (!id) return null;
    return document.getElementById(id);
  }

  function setVisible(tip, yes) {
    if (!tip) return;
    tip.classList.toggle('tooltip--hidden', !yes);
    tip.toggleAttribute('data-open', !!yes);
  }

  function measureTip(tip) {
    // Temporarily ensure measurable without flashing
    const prevTrans = tip.style.transition;
    const prevVis = tip.style.visibility;
    tip.style.transition = 'none';
    tip.style.visibility = 'hidden';
    tip.classList.remove('tooltip--hidden');
    const rect = tip.getBoundingClientRect();
    tip.classList.add('tooltip--hidden');
    tip.style.visibility = prevVis;
    tip.style.transition = prevTrans;
    return rect;
  }

  function clamp(n, min, max) { return Math.max(min, Math.min(max, n)); }

  function positionTip(trigger, tip) {
    const spacing = 8;
    const tRect = trigger.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // Ensure content is up to date before measuring
    const tipRect = measureTip(tip);

    // Prefer above; if no room, place below
    let top = tRect.top - tipRect.height - spacing;
    let side = 'top';
    if (top < 8) {
      top = tRect.bottom + spacing;
      side = 'bottom';
    }
    let left = tRect.left + (tRect.width / 2) - (tipRect.width / 2);
    left = clamp(left, 8, vw - tipRect.width - 8);
    // Prevent bottom overflow
    if (top + tipRect.height > vh - 8) {
      top = Math.max(8, vh - tipRect.height - 8);
    }

    tip.style.top = `${Math.round(top)}px`;
    tip.style.left = `${Math.round(left)}px`;
    tip.setAttribute('data-side', side);
  }

  function show(trigger) {
    const tip = getTip(trigger);
    if (!tip) return;
    // Put content
    const text = trigger.dataset.tooltip?.trim();
    if (text) tip.textContent = text;
    // Mark state
    trigger.setAttribute('aria-expanded', 'true');
    positionTip(trigger, tip);
    setVisible(tip, true);
    current = { trigger, tip };

    // Bind global listeners
    const offKey = on(document, 'keydown', (e) => {
      if (e.key === 'Escape') hide();
    });
    const offClick = on(document, 'mousedown', (e) => {
      if (!current) return;
      const withinTrigger = current.trigger.contains(e.target);
      const withinTip = current.tip.contains(e.target);
      if (!withinTrigger && !withinTip) hide();
    });
    const offTouch = on(document, 'touchstart', (e) => {
      if (!current) return;
      const withinTrigger = current.trigger.contains(e.target);
      const withinTip = current.tip.contains(e.target);
      if (!withinTrigger && !withinTip) hide();
    }, { passive: true });
    const offScroll = on(window, 'scroll', () => current && positionTip(current.trigger, current.tip));
    const offResize = on(window, 'resize', () => current && positionTip(current.trigger, current.tip));
    unbindDocHandlers = () => { offKey(); offClick(); offTouch(); offScroll(); offResize(); };
  }

  function hide() {
    if (!current) return;
    setVisible(current.tip, false);
    current.trigger.setAttribute('aria-expanded', 'false');
    unbindDocHandlers();
    current = null;
  }

  triggers.forEach((el) => {
    // Hover/focus show
    on(el, 'mouseenter', () => show(el));
    on(el, 'focus', () => show(el));
    // Mouseleave/blur hide (but allow click to toggle)
    on(el, 'mouseleave', () => { if (document.activeElement !== el) hide(); });
    on(el, 'blur', () => hide());
    // Tap/click toggle for mobile
    on(el, 'click', (e) => {
      e.preventDefault();
      if (current && current.trigger === el) hide(); else show(el);
    });
    on(el, 'touchstart', (e) => {
      e.preventDefault();
      if (current && current.trigger === el) hide(); else show(el);
    }, { passive: false });
  });
})();
