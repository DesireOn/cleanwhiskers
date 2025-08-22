(function (global) {
  function openMenu(doc, nav, openBtn, closeBtn) {
    doc.body.classList.add('no-scroll');
    nav.classList.add('is-open');
    nav.removeAttribute('hidden');
    nav.setAttribute('aria-hidden', 'false');
    openBtn.setAttribute('aria-expanded', 'true');
    openBtn.hidden = true;
    closeBtn.hidden = false;
    closeBtn.setAttribute('aria-expanded', 'true');
  }

  function closeMenu(doc, nav, openBtn, closeBtn) {
    doc.body.classList.remove('no-scroll');
    nav.classList.remove('is-open');
    nav.setAttribute('aria-hidden', 'true');
    nav.setAttribute('hidden', '');
    openBtn.setAttribute('aria-expanded', 'false');
    openBtn.hidden = false;
    closeBtn.hidden = true;
    closeBtn.setAttribute('aria-expanded', 'false');
  }

  function init(doc) {
    doc = doc || document;
    var openBtn = doc.getElementById('nav-open');
    var closeBtn = doc.getElementById('nav-close');
    var nav = doc.getElementById('primary-nav');
    if (!openBtn || !closeBtn || !nav) {
      return;
    }

    function onDocumentClick(e) {
      if (!nav.contains(e.target) && e.target !== openBtn && e.target !== closeBtn) {
        closeMenu(doc, nav, openBtn, closeBtn);
      }
    }

    function onKeyDown(e) {
      if (e.key === 'Escape') {
        closeMenu(doc, nav, openBtn, closeBtn);
        if (typeof openBtn.focus === 'function') {
          openBtn.focus();
          doc.activeElement = openBtn;
        }
      }
    }

    openBtn.addEventListener('click', function () {
      openMenu(doc, nav, openBtn, closeBtn);
    });

    closeBtn.addEventListener('click', function () {
      closeMenu(doc, nav, openBtn, closeBtn);
    });

    doc.addEventListener('click', onDocumentClick);
    doc.addEventListener('keydown', onKeyDown);
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initMobileNav: init, openMenu: openMenu, closeMenu: closeMenu };
  }
  if (global.document) {
    if (global.document.readyState === 'loading') {
      global.document.addEventListener('DOMContentLoaded', function () {
        init(global.document);
      });
    } else {
      init(global.document);
    }
  }
})(typeof window !== 'undefined' ? window : global);
