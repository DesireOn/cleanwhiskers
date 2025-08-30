(function (global) {
  // Keeps last focused element to restore on close
  var lastFocused = null;

  function getFocusableElements(container) {
    try {
      return Array.prototype.slice.call(container.querySelectorAll(
        'a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
      ));
    } catch (e) {
      return [];
    }
  }

  function openMenu(doc, nav, toggle, overlay) {
    lastFocused = doc.activeElement || toggle;
    doc.body.dataset.menuOpen = 'true';
    doc.body.style.overflow = 'hidden';
    nav.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    nav.setAttribute('aria-hidden', 'false');
    if (overlay) {
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
    }
    // Move focus to the first focusable element inside the menu
    var focusables = getFocusableElements(nav);
    if (focusables.length && typeof focusables[0].focus === 'function') {
      focusables[0].focus();
      doc.activeElement = focusables[0];
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
    // Restore focus to the toggle for accessibility
    if (typeof toggle.focus === 'function') {
      toggle.focus();
      doc.activeElement = toggle;
    } else if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
      doc.activeElement = lastFocused;
    }
  }

  function init(doc) {
    doc = doc || document;
    var toggle = doc.getElementById('nav-toggle');
    var nav = doc.getElementById('primary-nav');
    var overlay = doc.getElementById('nav-overlay');
    var closeBtn = nav.querySelector('.nav__close');
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
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        closeMenu(doc, nav, toggle, overlay);
        return;
      }
      // Focus trap while menu is open
      if (e.key === 'Tab' && nav.classList.contains('is-open')) {
        var focusables = getFocusableElements(nav);
        if (focusables.length === 0) return;
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        var active = doc.activeElement;
        if (e.shiftKey) {
          if (active === first || !nav.contains(active)) {
            e.preventDefault();
            if (typeof last.focus === 'function') last.focus();
            doc.activeElement = last;
          }
        } else {
          if (active === last) {
            e.preventDefault();
            if (typeof first.focus === 'function') first.focus();
            doc.activeElement = first;
          }
        }
      }
    }

    if (overlay) {
      overlay.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay);
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay);
      });
    }

    var links = nav.querySelectorAll('a');
    links.forEach(function (link) {
      link.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay);
      });
    });

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
