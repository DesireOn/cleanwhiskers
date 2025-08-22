(function (global) {
  function openMenu(doc, nav, toggle, overlay) {
    doc.body.dataset.menuOpen = 'true';
    doc.body.style.overflow = 'hidden';
    nav.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    nav.setAttribute('aria-hidden', 'false');
    if (overlay) {
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
    }
  }

  function closeMenu(doc, nav, toggle, overlay) {
    delete doc.body.dataset.menuOpen;
    doc.body.style.overflow = '';
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');
    if (overlay) {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }
  }

  function init(doc) {
    doc = doc || document;
    var toggle = doc.getElementById('nav-toggle');
    var nav = doc.getElementById('primary-nav');
    var overlay = doc.getElementById('nav-overlay');
    if (!toggle || !nav) {
      return;
    }
    doc.body.classList.add('js');

    function onDocumentClick(e) {
      if (!nav.contains(e.target) && !toggle.contains(e.target)) {
        closeMenu(doc, nav, toggle, overlay);
      }
    }

    function onKeyDown(e) {
      if (e.key === 'Escape') {
        closeMenu(doc, nav, toggle, overlay);
        if (typeof toggle.focus === 'function') {
          toggle.focus();
          doc.activeElement = toggle;
        }
      }
    }

    if (overlay) {
      overlay.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay);
      });
    }

    toggle.addEventListener('click', function () {
      if (nav.classList.contains('is-open')) {
        closeMenu(doc, nav, toggle, overlay);
      } else {
        openMenu(doc, nav, toggle, overlay);
      }
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
