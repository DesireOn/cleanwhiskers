// Progressive enhancement: inline errors and gentle shake on invalid submit

function ensureErrorEl(field) {
  let msg = field.parentElement.querySelector('.field-error');
  if (!msg) {
    msg = document.createElement('div');
    msg.className = 'field-error';
    msg.setAttribute('aria-live', 'polite');
    msg.setAttribute('role', 'status');
    field.parentElement.appendChild(msg);
  }
  return msg;
}

function findShakeContainer(form) {
  // Prefer the second CTA card if present
  const card = form.closest('.second-cta__card');
  if (card) return card;
  // Fallback to the hero inner container
  const heroInner = form.closest('.hero__inner') || form.closest('.hero');
  return heroInner || form;
}

document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form.lead-capture-form');
  forms.forEach((form) => {
    const fields = form.querySelectorAll('input[required], select[required]');
    const submitBtn = form.querySelector('button[type="submit"], .hero__cta.btn');

    fields.forEach((field) => {
      field.addEventListener('invalid', (e) => {
        e.preventDefault();
        const errorEl = ensureErrorEl(field);
        errorEl.textContent = field.validationMessage;
        field.setAttribute('aria-invalid', 'true');
      });

      field.addEventListener('input', () => {
        const msg = field.parentElement.querySelector('.field-error');
        if (msg) msg.textContent = '';
        field.removeAttribute('aria-invalid');
      });

      field.addEventListener('blur', () => {
        if (field.checkValidity()) {
          const msg = field.parentElement.querySelector('.field-error');
          if (msg) msg.textContent = '';
          field.removeAttribute('aria-invalid');
        }
      });
    });

    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault();
        // Show messages for all invalid required fields
        const invalids = Array.from(fields).filter((f) => !f.checkValidity());
        invalids.forEach((f) => {
          const errorEl = ensureErrorEl(f);
          errorEl.textContent = f.validationMessage;
          f.setAttribute('aria-invalid', 'true');
        });
        // Focus the first invalid
        if (invalids[0]) invalids[0].focus();

        // Shake the container
        const container = findShakeContainer(form);
        if (container) {
          container.classList.remove('is-shaking');
          // Force reflow to restart animation if already applied
          void container.offsetWidth;
          container.classList.add('is-shaking');
          container.addEventListener(
            'animationend',
            () => container.classList.remove('is-shaking'),
            { once: true }
          );
        }
        // Ensure submit button isn't stuck in loading state on invalid
        if (submitBtn) {
          submitBtn.classList.remove('is-loading');
          submitBtn.removeAttribute('aria-busy');
          submitBtn.disabled = false;
          const original = submitBtn.getAttribute('data-original-label');
          if (original) submitBtn.textContent = original;
        }
      } else {
        // Valid submission: show loading state to prevent duplicate submits
        if (submitBtn && !submitBtn.classList.contains('is-loading')) {
          submitBtn.setAttribute('data-original-label', submitBtn.textContent.trim());
          submitBtn.classList.add('is-loading');
          submitBtn.setAttribute('aria-busy', 'true');
          submitBtn.disabled = true;
          // Keep text but prepend a subtle spinner via CSS
        }
      }
    });
  });
});
