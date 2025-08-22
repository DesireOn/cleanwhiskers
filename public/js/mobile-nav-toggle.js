(function (global) {
  function initMobileNav(doc) {
    doc = doc || document;
    var openBtn = doc.getElementById('nav-open');
    var closeBtn = doc.getElementById('nav-close');
    var nav = doc.getElementById('primary-nav');
    if (!openBtn || !closeBtn || !nav) {
      return;
    }

    doc.body.classList.add('js');

    function openMenu() {
      nav.hidden = false;
      nav.classList.add('is-open');
      openBtn.hidden = true;
      closeBtn.hidden = false;
      openBtn.setAttribute('aria-expanded', 'true');
      closeBtn.setAttribute('aria-expanded', 'true');
      doc.body.classList.add('nav-open');
      var firstLink = nav.querySelector('a');
      if (firstLink && typeof firstLink.focus === 'function') {
        firstLink.focus();
      }
    }

    function closeMenu() {
      nav.classList.remove('is-open');
      nav.hidden = true;
      openBtn.hidden = false;
      closeBtn.hidden = true;
      openBtn.setAttribute('aria-expanded', 'false');
      closeBtn.setAttribute('aria-expanded', 'false');
      doc.body.classList.remove('nav-open');
      if (typeof openBtn.focus === 'function') {
        openBtn.focus();
      }
    }

    openBtn.addEventListener('click', openMenu);
    closeBtn.addEventListener('click', closeMenu);
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !nav.hidden) {
        closeMenu();
      }
    });
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initMobileNav: initMobileNav };
  } else if (global.document) {
    global.document.addEventListener('DOMContentLoaded', function () {
      initMobileNav(global.document);
    });
  }
})(this);
