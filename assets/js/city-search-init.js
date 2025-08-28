import initCityAutocomplete from './city-autocomplete.js';

document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('.city-input');
  if (inputs && inputs.length) {
    initCityAutocomplete(inputs);
  }
});

