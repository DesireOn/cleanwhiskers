(function (global) {
  function initCtaButton(doc, fetchFn, win) {
    doc = doc || document;
    fetchFn = fetchFn || (typeof fetch !== 'undefined' ? fetch.bind(global) : null);
    win = win || global;
    var button = doc.querySelector('.cta-banner__link--owners');
    if (!button) {
      return;
    }
    var spinner = button.querySelector('.spinner');
    if (!spinner) {
      spinner = doc.createElement('span');
      spinner.className = 'spinner';
      spinner.hidden = true;
      button.appendChild(spinner);
    }
    var errorEl = doc.createElement('span');
    errorEl.className = 'cta-button__error';
    errorEl.setAttribute('role', 'status');
    errorEl.setAttribute('aria-live', 'polite');
    errorEl.hidden = true;
    button.after(errorEl);
    button.classList.remove('is-loading');
    button.removeAttribute('aria-disabled');
    spinner.hidden = true;
    var pending = false;
    button.addEventListener('click', function (e) {
      if (pending) {
        if (e && typeof e.preventDefault === 'function') {
          e.preventDefault();
        }
        return;
      }
      pending = true;
      button.classList.add('is-loading');
      button.setAttribute('aria-disabled', 'true');
      spinner.hidden = false;
      var href = button.getAttribute('href');
      if (fetchFn) {
        fetchFn(href, { method: 'HEAD' })
          .then(function () {
            win.location.href = href;
          })
          .catch(function () {
            pending = false;
            button.classList.remove('is-loading');
            button.removeAttribute('aria-disabled');
            spinner.hidden = true;
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
