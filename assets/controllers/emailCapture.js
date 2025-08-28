const initEmailCapture = () => {
    const form = document.querySelector('.email-capture__form');
    if (!form) {
        return;
    }

    const messageEl = form.querySelector('.email-capture__message');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const name = formData.get('name').trim();
        const email = formData.get('email').trim();
        const dogBreed = formData.get('dogBreed').trim();
        const website = formData.get('website').trim();

        if (!name || !email || website) {
            messageEl.textContent = 'Please provide a valid name and email.';
            messageEl.hidden = false;
            return;
        }

        try {
            const response = await fetch('/lead-capture', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name,
                    email,
                    dogBreed,
                    city: form.dataset.city,
                    service: form.dataset.service,
                    website,
                }),
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            messageEl.textContent = 'Thanks! We\'ll be in touch.';
            form.reset();
        } catch (err) {
            messageEl.textContent = 'Submission failed. Please try again later.';
        }

        messageEl.hidden = false;
    });
};

window.addEventListener('DOMContentLoaded', initEmailCapture);

