// Lightweight DOM utilities (no dependencies)

/**
 * Query a single element within an optional context.
 * @param {string} selector
 * @param {ParentNode} [ctx=document]
 * @returns {Element|null}
 */
export function qs(selector, ctx = document) {
  return ctx.querySelector(selector);
}

/**
 * Query all elements as an array within an optional context.
 * @param {string} selector
 * @param {ParentNode} [ctx=document]
 * @returns {Element[]}
 */
export function qsa(selector, ctx = document) {
  return Array.from(ctx.querySelectorAll(selector));
}

/**
 * Add an event listener with a small convenience wrapper.
 * @param {EventTarget} target
 * @param {string} type
 * @param {Function} handler
 * @param {AddEventListenerOptions|boolean} [options]
 */
export function on(target, type, handler, options) {
  if (!target) return () => {};
  target.addEventListener(type, handler, options);
  return () => target.removeEventListener(type, handler, options);
}

/**
 * Smoothly scroll to an element (or y position), with safe fallback.
 * @param {Element|number} elOrY
 */
export function smoothScrollTo(elOrY) {
  const y = typeof elOrY === 'number'
    ? elOrY
    : (elOrY?.getBoundingClientRect ? (window.scrollY + elOrY.getBoundingClientRect().top) : 0);

  try {
    window.scrollTo({ top: y, behavior: 'smooth' });
  } catch (_) {
    // Fallback for older browsers
    window.scrollTo(0, y);
  }
}

