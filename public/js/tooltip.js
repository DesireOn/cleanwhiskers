document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tooltip]').forEach((trigger) => {
        const id = trigger.getAttribute('aria-describedby');
        const tooltip = document.getElementById(id);
        if (!tooltip) {
            return;
        }

        const show = () => tooltip.setAttribute('aria-hidden', 'false');
        const hide = () => tooltip.setAttribute('aria-hidden', 'true');
        const toggle = (e) => {
            e.preventDefault();
            if (tooltip.getAttribute('aria-hidden') === 'true') {
                show();
            } else {
                hide();
            }
        };

        trigger.addEventListener('mouseenter', show);
        trigger.addEventListener('focus', show);
        trigger.addEventListener('mouseleave', hide);
        trigger.addEventListener('blur', hide);
        trigger.addEventListener('touchstart', toggle);
    });
});
