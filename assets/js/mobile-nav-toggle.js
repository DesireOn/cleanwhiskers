(function (global) {
  var FOCUSABLE_SELECTOR = [
    'a[href]:not([tabindex="-1"])',
    'button:not([disabled]):not([tabindex="-1"])',
    '[tabindex]:not([tabindex="-1"])'
  ].join(',');

  function openMenu(doc, nav, toggle, overlay, state) {
    state = state || {};
    state.previouslyFocused = doc.activeElement;
    doc.body.dataset.menuOpen = 'true';
    doc.body.style.overflow = 'hidden';
    nav.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    nav.setAttribute('aria-hidden', 'false');
    if (overlay) {
      overlay.classList.add('is-open');
      overlay.setAttribute('aria-hidden', 'false');
    }
    var focusables = nav.querySelectorAll(FOCUSABLE_SELECTOR);
    if (focusables.length > 0 && typeof focusables[0].focus === 'function') {
      focusables[0].focus();
    }
  }

  function closeMenu(doc, nav, toggle, overlay, state) {
    state = state || {};
    delete doc.body.dataset.menuOpen;
    doc.body.style.overflow = '';
    nav.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    nav.setAttribute('aria-hidden', 'true');
    if (overlay) {
      overlay.classList.remove('is-open');
      overlay.setAttribute('aria-hidden', 'true');
    }
    if (state.previouslyFocused && typeof state.previouslyFocused.focus === 'function') {
      state.previouslyFocused.focus();
    } else if (typeof toggle.focus === 'function') {
      toggle.focus();
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
    var closeBtn = nav.querySelector('.nav__close');
    var focusState = { previouslyFocused: null };
    doc.body.classList.add('js');

    function onDocumentClick(e) {
      if (!nav.contains(e.target) && !toggle.contains(e.target)) {
        closeMenu(doc, nav, toggle, overlay, focusState);
      }
    }

    function onKeyDown(e) {
      if (e.key === 'Escape' && nav.classList.contains('is-open')) {
        closeMenu(doc, nav, toggle, overlay, focusState);
      }
    }

    function onTrapTab(e) {
      if (e.key !== 'Tab' || !nav.classList.contains('is-open')) {
        return;
      }
      var focusables = nav.querySelectorAll(FOCUSABLE_SELECTOR);
      if (focusables.length === 0) {
        e.preventDefault();
        toggle.focus();
        return;
      }
      var first = focusables[0];
      var last = focusables[focusables.length - 1];
      if (e.shiftKey && doc.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && doc.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }

    if (overlay) {
      overlay.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay, focusState);
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay, focusState);
      });
    }

    var links = nav.querySelectorAll('a');
    links.forEach(function (link) {
      link.addEventListener('click', function () {
        closeMenu(doc, nav, toggle, overlay, focusState);
      });
    });

    toggle.addEventListener('click', function () {
      if (nav.classList.contains('is-open')) {
        closeMenu(doc, nav, toggle, overlay, focusState);
      } else {
        openMenu(doc, nav, toggle, overlay, focusState);
      }
    });

    doc.addEventListener('click', onDocumentClick);
    doc.addEventListener('keydown', onKeyDown);
    doc.addEventListener('keydown', onTrapTab, true);
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      initMobileNav: init,
      openMenu: openMenu,
      closeMenu: closeMenu
    };
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
