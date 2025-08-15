(function (global) {
  function initLazyImages(doc) {
    doc = doc || document;
    var imgs = doc.querySelectorAll('img[data-skeleton]');
    imgs.forEach(function (img) {
      img.classList.add('skeleton');
      if (img.complete) {
        img.classList.remove('skeleton');
      } else {
        img.addEventListener('load', function () {
          img.classList.remove('skeleton');
        });
      }
    });
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { initLazyImages: initLazyImages };
  } else {
    global.document.addEventListener('DOMContentLoaded', function () {
      initLazyImages(global.document);
    });
  }
})(this);
