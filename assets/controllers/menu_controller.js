import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu'];

    connect() {
        this.isOpen = false;
        this.menuTarget.classList.add('is-ready');
        this.close();
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        this.menuTarget.classList.add('is-open');
        this.element.setAttribute('aria-expanded', 'true');
        this.isOpen = true;
        this.focusable = this.menuTarget.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');
        if (this.focusable.length > 0) {
            this.focusable[0].focus();
        }
        this.boundTrap = this.trap.bind(this);
        document.addEventListener('keydown', this.boundTrap);
    }

    close() {
        this.menuTarget.classList.remove('is-open');
        this.element.setAttribute('aria-expanded', 'false');
        this.isOpen = false;
        document.removeEventListener('keydown', this.boundTrap);
    }

    trap(event) {
        if (!this.isOpen) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
            this.element.focus();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const first = this.focusable[0];
        const last = this.focusable[this.focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
}
