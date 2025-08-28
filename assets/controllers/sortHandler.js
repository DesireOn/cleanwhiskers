// Placeholder: hook up sorting dropdown when controls region exists
import { qs, on } from '../helpers/dom.js';

(function initSortHandler() {
  const controls = qs('#controls');
  if (!controls) return;
  const select = controls.querySelector('select#rating');
  if (!select) return;
  // Example: auto-submit on change (non-breaking)
  on(select, 'change', () => {
    const form = select.closest('form');
    if (form) form.submit();
  });
})();

