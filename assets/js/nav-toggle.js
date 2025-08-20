(function (global) {
  function focusTrap(doc, nav, e) {
    if (e.key !== 'Tab' || doc.body.dataset.menuOpen !== 'true') {
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

  function openMenu(doc, menu, toggle) {
    doc.body.dataset.menuOpen = 'true';
    doc.body.style.overflow = 'hidden';
    toggle.setAttribute('aria-expanded', 'true');
    menu.setAttribute('aria-hidden', 'false');
    var first = menu.querySelector('a, button, [tabindex]:not([tabindex="-1"])');
    if (first && typeof first.focus === 'function') {
      first.focus();
      doc.activeElement = first;
    }
  }

  function closeMenu(doc, menu, toggle) {
    delete doc.body.dataset.menuOpen;
    doc.body.style.overflow = '';
    toggle.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');
    if (typeof toggle.focus === 'function') {
      toggle.focus();
      doc.activeElement = toggle;
    }
  }

  function initNavToggle(doc) {
    doc = doc || document;
    var nav = doc.getElementById('primary-nav');
    var menu = nav ? nav.querySelector('.nav') : null;
    var toggle = doc.getElementById('nav-toggle');
    if (!menu || !toggle) {
      return;
    }
    var mq = global.matchMedia('(min-width: 768px)');
    var syncMenu = function (e) {
      if (e.matches) {
        menu.removeAttribute('aria-hidden');
        doc.body.style.overflow = '';
        toggle.setAttribute('aria-expanded', 'false');
      } else {
        closeMenu(doc, menu, toggle);
      }
    };
    if (mq.addEventListener) {
      mq.addEventListener('change', syncMenu);
    } else if (mq.addListener) {
      mq.addListener(syncMenu);
    }
    syncMenu(mq);
    var onKeyDown = function (e) {
      if (e.key === 'Escape') {
        closeMenu(doc, menu, toggle);
      } else if (doc.body.dataset.menuOpen === 'true') {
        focusTrap(doc, menu, e);
      }
    };
    var onClickOutside = function (e) {
      if (!menu.contains(e.target) && e.target !== toggle) {
        closeMenu(doc, menu, toggle);
      }
    };
    toggle.addEventListener('click', function () {
      var expanded = toggle.getAttribute('aria-expanded') === 'true';
      if (expanded) {
        closeMenu(doc, menu, toggle);
      } else {
        openMenu(doc, menu, toggle);
      }
    });
    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        closeMenu(doc, menu, toggle);
      });
    });
    doc.addEventListener('keydown', onKeyDown);
    doc.addEventListener('click', onClickOutside);
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initNavToggle: initNavToggle, openMenu: openMenu, closeMenu: closeMenu, focusTrap: focusTrap };
  } else {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        initNavToggle(document);
      });
    } else {
      initNavToggle(document);
    }
  }
})(this);
