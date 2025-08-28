(function (global) {
  function initHero(doc) {
    doc = doc || document;
    var pin = doc.getElementById('use-location');
    var input = doc.getElementById('city');
    if (!pin || !input) {
      return;
    }
    pin.addEventListener('click', function () {
      input.value = '';
      if (typeof input.focus === 'function') {
        input.focus();
      }
    });
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initHero: initHero };
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      initHero(document);
    });
  }
})(this);
