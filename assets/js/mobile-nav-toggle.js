(function (global) {
  function openMenu(doc, nav, toggle) {
    nav.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    nav.setAttribute('aria-hidden', 'false');
  }

  function closeMenu(doc, nav, toggle) {
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');
  }

  function init(doc) {
    doc = doc || document;
    var toggle = doc.getElementById('nav-toggle');
    var nav = doc.getElementById('primary-nav');
    if (!toggle || !nav) {
      return;
    }
    doc.body.classList.add('js');

    function onDocumentClick(e) {
      if (!nav.contains(e.target) && e.target !== toggle) {
        closeMenu(doc, nav, toggle);
      }
    }

    function onKeyDown(e) {
      if (e.key === 'Escape') {
        closeMenu(doc, nav, toggle);
        if (typeof toggle.focus === 'function') {
          toggle.focus();
          doc.activeElement = toggle;
        }
      }
    }

    toggle.addEventListener('click', function () {
      if (nav.classList.contains('is-open')) {
        closeMenu(doc, nav, toggle);
      } else {
        openMenu(doc, nav, toggle);
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
