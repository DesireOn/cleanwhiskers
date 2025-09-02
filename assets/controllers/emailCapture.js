// Placeholder: safely wire up an email capture form if present
import { qs, on } from '../helpers/dom.js';

(function initEmailCapture() {
  const section = qs('#email-capture');
  if (!section) return;
  const form = section.querySelector('form');
  if (!form) return;
  on(form, 'submit', (e) => {
    // Allow default submit; ensure basic validation without console errors
    const email = form.querySelector('input[type="email"]');
    if (email && !email.value) {
      e.preventDefault();
      email.focus();
    }
  });
})();

