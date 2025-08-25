export function setLoading(el, loading = true) {
  if (!el) return;
  const spinner = el.querySelector('.spinner') || el.querySelector('[role="status"], .mobile-cta__spinner');
  el.classList.toggle('is-loading', loading);
  el.setAttribute('aria-busy', loading ? 'true' : 'false');
  if (spinner) spinner.hidden = !loading;
}
