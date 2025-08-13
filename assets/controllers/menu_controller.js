import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'list'];

    connect() {
        this.element.classList.add('has-js');
    }

    toggle() {
        const isOpen = this.element.classList.toggle('is-open');
        this.buttonTarget.setAttribute('aria-expanded', String(isOpen));

        if (isOpen) {
            this.activateFocusTrap();
            const first = this.focusables[0];
            first && first.focus();
        } else {
            this.deactivateFocusTrap();
            this.buttonTarget.focus();
        }
    }

    activateFocusTrap() {
        this.focusables = this.getFocusable();
        this.boundHandleKeydown = this.handleKeydown.bind(this);
        this.listTarget.addEventListener('keydown', this.boundHandleKeydown);
    }

    deactivateFocusTrap() {
        this.listTarget.removeEventListener('keydown', this.boundHandleKeydown);
        this.boundHandleKeydown = null;
    }

    handleKeydown(event) {
        if (event.key !== 'Tab') {
            return;
        }

        const first = this.focusables[0];
        const last = this.focusables[this.focusables.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    getFocusable() {
        return Array.from(this.listTarget.querySelectorAll('a, button, input, [tabindex]:not([tabindex="-1"])')).filter(
            (el) => !el.hasAttribute('disabled')
        );
    }
}
