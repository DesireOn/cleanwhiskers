import { setLoading } from './loading.js';

document.addEventListener('DOMContentLoaded', () => {
  const link = document.querySelector('.nav__link[href*="app_search_redirect"]');
  if (!link) return;
  link.addEventListener('click', async (e) => {
    const spin = link.querySelector('.spinner') || document.createElement('span');
    if (!spin.classList.contains('spinner')) {
      spin.className = 'spinner';
      spin.hidden = true;
      link.appendChild(spin);
    }
    const btnLike = link; // treat as target
    setLoading(btnLike, true);
    try {
      await fetch(link.href, { method: 'HEAD' });
      window.location.assign(link.href);
    } catch {
      setLoading(btnLike, false);
    }
    e.preventDefault();
  });
});
