(function (global) {
  function focusTrap(doc, nav, e) {
    if (e.key !== 'Tab') {
      return;
    }
    var focusable = nav.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
    if (!focusable.length) {
      return;
    }
    var first = focusable[0];
    var last = focusable[focusable.length - 1];
    if (e.shiftKey && doc.activeElement === first) {
      e.preventDefault();
      last.focus();
      doc.activeElement = last;
    } else if (!e.shiftKey && doc.activeElement === last) {
      e.preventDefault();
      first.focus();
      doc.activeElement = first;
    }
  }

  function openMenu(doc, nav, toggle) {
    doc.body.dataset.menuOpen = 'true';
    doc.body.style.overflow = 'hidden';
    toggle.setAttribute('aria-expanded', 'true');
    var first = nav.querySelector('a, button, [tabindex]:not([tabindex="-1"])');
    if (first && typeof first.focus === 'function') {
      first.focus();
      doc.activeElement = first;
    }
  }

  function closeMenu(doc, nav, toggle) {
    delete doc.body.dataset.menuOpen;
    doc.body.style.overflow = '';
    toggle.setAttribute('aria-expanded', 'false');
  }

  function initNavToggle(doc) {
    doc = doc || document;
    var nav = doc.getElementById('primary-nav');
    var toggle = doc.getElementById('nav-toggle');
    if (!nav || !toggle) {
      return;
    }
    var onKeyDown = function (e) {
      if (e.key === 'Escape') {
        closeMenu(doc, nav, toggle);
        if (typeof toggle.focus === 'function') {
          toggle.focus();
          doc.activeElement = toggle;
        }
      } else {
        focusTrap(doc, nav, e);
      }
    };
    var onClickOutside = function (e) {
      if (!nav.contains(e.target) && e.target !== toggle) {
        closeMenu(doc, nav, toggle);
      }
    };
    toggle.addEventListener('click', function () {
      if (doc.body.dataset.menuOpen === 'true') {
        closeMenu(doc, nav, toggle);
      } else {
        openMenu(doc, nav, toggle);
      }
    });
    doc.addEventListener('keydown', onKeyDown);
    doc.addEventListener('click', onClickOutside);
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initNavToggle: initNavToggle, openMenu: openMenu, closeMenu: closeMenu, focusTrap: focusTrap };
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      initNavToggle(document);
    });
  }
})(this);
