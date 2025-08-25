import { setLoading } from './loading.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#search-form');
  const submit = document.querySelector('#search-submit');
  if (!form || !submit) return;
  form.addEventListener('submit', () => setLoading(submit, true), { passive: true });
});
