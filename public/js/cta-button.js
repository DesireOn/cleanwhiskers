(function (global) {
  function initCtaButton(doc, fetchFn, win) {
    doc = doc || document;
    fetchFn = fetchFn || (typeof fetch !== 'undefined' ? fetch.bind(global) : null);
    win = win || global;
    var button = doc.querySelector('.cta-banner__link--owners');
    if (!button) {
      return;
    }
    var errorEl = doc.createElement('span');
    errorEl.className = 'cta-button__error';
    errorEl.setAttribute('role', 'status');
    errorEl.setAttribute('aria-live', 'polite');
    errorEl.hidden = true;
    button.after(errorEl);
    button.removeAttribute('aria-disabled');
    var pending = false;
    button.addEventListener('click', function (e) {
      if (pending) {
        if (e && typeof e.preventDefault === 'function') {
          e.preventDefault();
        }
        return;
      }
      pending = true;
      button.setAttribute('aria-disabled', 'true');
      var href = button.getAttribute('href');
      if (fetchFn) {
        fetchFn(href, { method: 'HEAD' })
          .then(function () {
            win.location.href = href;
          })
          .catch(function () {
            pending = false;
            button.removeAttribute('aria-disabled');
            errorEl.textContent = 'Please try again';
            errorEl.hidden = false;
          });
      }
    });
  }
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initCtaButton: initCtaButton };
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      initCtaButton(document);
    });
  }
})(this);
