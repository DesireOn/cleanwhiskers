import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'list', 'hidden'];
    static values = { url: String };

    connect() {
        this.timer = null;
    }

    fetch(event) {
        const q = this.inputTarget.value.trim();
        clearTimeout(this.timer);
        if (q.length < 2) {
            this.listTarget.innerHTML = '';
            this.hiddenTarget.value = '';
            this.inputTarget.dispatchEvent(new Event('change'));
            return;
        }

        this.timer = setTimeout(() => {
            fetch(`${this.urlValue}?q=${encodeURIComponent(q)}`)
                .then(response => response.json())
                .then(data => {
                    this.listTarget.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item.name;
                        li.addEventListener('click', () => {
                            this.inputTarget.value = item.name;
                            this.hiddenTarget.value = item.slug;
                            this.listTarget.innerHTML = '';
                            this.inputTarget.dispatchEvent(new Event('change'));
                        });
                        this.listTarget.appendChild(li);
                    });
                });
        }, 300);
    }
}
