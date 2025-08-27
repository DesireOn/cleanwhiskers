import { Controller } from '@hotwired/stimulus';
import initCityAutocomplete from '../js/city-autocomplete.js';

export default class extends Controller {
    connect() {
        const inputs = document.querySelectorAll('.city-input');
        initCityAutocomplete(inputs);
    }
}
