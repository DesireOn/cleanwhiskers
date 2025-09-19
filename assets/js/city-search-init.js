import initCityAutocomplete from './city-autocomplete.js';

document.addEventListener('DOMContentLoaded', () => {
  const allInputs = Array.from(document.querySelectorAll('.city-input'));
  if (!allInputs.length) return;

  // Remove sticky city autocomplete on desktop viewports
  const isDesktop = window.matchMedia('(min-width: 769px)').matches;
  const inputs = isDesktop
    ? allInputs.filter((el) => el.id !== 'sticky-city')
    : allInputs;

  if (inputs.length) {
    initCityAutocomplete(inputs);
  }
});
