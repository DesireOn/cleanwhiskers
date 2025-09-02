(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var links = document.querySelectorAll('article a[target="_blank"]');
    links.forEach(function (link) {
      if (!link.rel.includes('noopener')) {
        link.rel += (link.rel ? ' ' : '') + 'noopener';
      }
    });
  });
})();
