// Focus the city input after scrolling it into view, with a subtle overlay highlight
// - Handles clicks on anchors linking to #search-form or #city
// - Exposes window.CW_focusCityAfterScroll(targetEl) for other controllers (e.g., heroScroll)

import { qs, on } from '../helpers/dom.js';

function getCityInput() {
  // Prefer primary hero city field, fallback to sticky city input
  return (
    qs('#city') ||
    qs('#sticky-city') ||
    qs('.city-input')
  );
}

function ensureCentered(el) {
  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  try {
    el.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'center', inline: 'nearest' });
  } catch (_) {
    // ignore if not supported
  }
}

function waitForScrollEnd(maxMs = 800) {
  return new Promise((resolve) => {
    let lastY = window.scrollY;
    let elapsed = 0;
    const step = 50;

    const timer = setInterval(() => {
      const y = window.scrollY;
      elapsed += step;
      if (Math.abs(y - lastY) < 2 || elapsed >= maxMs) {
        clearInterval(timer);
        resolve();
      }
      lastY = y;
    }, step);

    // Use the scrollend event when available for faster reaction
    const off = on(window, 'scrollend', () => {
      clearInterval(timer);
      off();
      resolve();
    }, { once: true });
  });
}

function createOverlay() {
  let overlay = document.createElement('div');
  overlay.className = 'focus-overlay';
  document.body.appendChild(overlay);

  requestAnimationFrame(() => {
    overlay.classList.add('is-active');
  });

  return overlay;
}

function elevate(el) {
  el.classList.add('focus-elevated');
  el.classList.add('focus-highlight');
}

function deElevate(el) {
  el.classList.remove('focus-elevated');
  el.classList.remove('focus-highlight');
}

function getLiveRegion() {
  let region = document.getElementById('sr-announcer');
  if (!region) {
    region = document.createElement('div');
    region.id = 'sr-announcer';
    region.setAttribute('role', 'status');
    region.setAttribute('aria-live', 'polite');
    region.className = 'visually-hidden';
    document.body.appendChild(region);
  }
  return region;
}

function announce(msg) {
  const region = getLiveRegion();
  region.textContent = '';
  setTimeout(() => { region.textContent = msg; }, 30);
}

// Removed visual coachmark to avoid overlapping suggestions list

let isActive = false;

async function centerAndFocusCity(targetEl) {
  if (isActive) return;
  const input = getCityInput();
  if (!input) return;

  // If targetEl provided, center that; otherwise center the input itself
  ensureCentered(targetEl || input);
  await waitForScrollEnd();

  const overlay = createOverlay();
  elevate(input);
  isActive = true;

  try {
    input.focus({ preventScroll: true });
  } catch (_) {
    input.focus();
  }

  const cleanup = () => {
    deElevate(input);
    overlay.classList.remove('is-active');
    // remove after transition
    setTimeout(() => overlay.remove(), 200);
    offEsc();
    offBlur();
    offClick();
    offInput();
    isActive = false;
  };

  const offEsc = on(window, 'keydown', (e) => {
    if (e.key === 'Escape') cleanup();
  });

  const offBlur = on(input, 'blur', cleanup, { once: true });
  const offClick = on(overlay, 'click', cleanup);

  // Assist and dismiss on input
  const offInput = on(input, 'input', () => {
    cleanup();
  });

  // Announce for screen readers only
  announce('Focus moved to city input. Start typing to see suggestions.');
}

// Public hook for other controllers
window.CW_focusCityAfterScroll = (targetEl) => centerAndFocusCity(targetEl);

// Intercept anchor clicks to #search-form or #city to center+focus elegantly
on(document, 'click', (e) => {
  const a = e.target.closest('a[href]');
  if (!a) return;
  const href = a.getAttribute('href');
  if (href === '#search-form' || href === '#city') {
    const target = document.querySelector(href);
    e.preventDefault();
    centerAndFocusCity(target);
  }
});
