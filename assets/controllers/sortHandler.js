// Sort dropdown: updates URL ?sort= and reloads, preserving other params.
(function initSortHandler() {
  const select = document.querySelector('#sort-control select#sort');
  if (!select) return;

  const allowed = new Set(['recommended', 'price_asc', 'rating_desc']);

  select.addEventListener('change', () => {
    const value = String(select.value || '').trim();
    if (!allowed.has(value)) return; // ignore unexpected values

    const url = new URL(window.location.href);
    url.searchParams.set('sort', value);

    // Reload with updated params, preserving path and other params
    window.location.assign(url.toString());
  });
})();
