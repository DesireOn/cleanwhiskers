(function (global) {
  function initCtaButton(doc, fetchFn, win) {
    doc = doc || document;
    fetchFn = fetchFn || (typeof fetch !== 'undefined' ? fetch.bind(global) : null);
    win = win || global;
    var buttons = doc.querySelectorAll(
      '.cta-banner__link--owners, .sticky-cta__btn'
    );
    if (!buttons.length) {
      return;
    }
    function setupButton(button) {
      var errorEl = doc.createElement('span');
      errorEl.className = 'cta-button__error';
      errorEl.setAttribute('role', 'status');
      errorEl.setAttribute('aria-live', 'polite');
      errorEl.hidden = true;
      button.after(errorEl);
      button.removeAttribute('aria-disabled');
      var pending = false;

      function addSpinner() {
        var spinner = doc.createElement('span');
        spinner.className = 'spinner';
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-live', 'polite');
        var hidden = doc.createElement('span');
        hidden.className = 'visually-hidden';
        hidden.textContent = 'Loading';
        spinner.appendChild(hidden);
        button.insertBefore(spinner, button.firstChild);
        button.setAttribute('aria-busy', 'true');
        return spinner;
      }

      function removeSpinner(spinner) {
        button.removeAttribute('aria-busy');
        if (spinner && spinner.parentNode === button) {
          button.removeChild(spinner);
        }
      }

      button.addEventListener('click', function (e) {
        if (pending) {
          if (e && typeof e.preventDefault === 'function') {
            e.preventDefault();
          }
          return;
        }
        pending = true;
        button.setAttribute('aria-disabled', 'true');
        var spinner = addSpinner();
        var href = button.getAttribute('href');

        function reset() {
          pending = false;
          button.removeAttribute('aria-disabled');
          removeSpinner(spinner);
        }

        if (fetchFn) {
          fetchFn(href, { method: 'HEAD' })
            .then(function () {
              win.location.href = href;
            })
            .catch(function () {
              errorEl.textContent = 'Please try again';
              errorEl.hidden = false;
            })
            .finally(reset);
        } else {
          reset();
          win.location.href = href;
        }
      });
    }
    for (var i = 0; i < buttons.length; i++) {
      setupButton(buttons[i]);
    }
  }
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initCtaButton: initCtaButton };
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      initCtaButton(document);
    });
  }
})(this);
