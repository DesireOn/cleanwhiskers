const endpoints = {
    city: '/api/autocomplete/cities',
    service: '/api/autocomplete/services',
};

function initAutocomplete(input) {
    const type = input.dataset.autocomplete;
    const hidden = document.getElementById(type + 'Slug');
    const list = document.createElement('ul');
    list.className = 'autocomplete-list';
    list.style.position = 'absolute';
    list.style.zIndex = '1000';
    list.hidden = true;
    input.parentNode.appendChild(list);

    let index = -1;
    let items = [];
    let debounce;

    input.addEventListener('input', () => {
        hidden.value = '';
        const q = input.value.trim().toLowerCase();
        if (q.length < 2) {
            list.hidden = true;
            return;
        }
        clearTimeout(debounce);
        debounce = setTimeout(async () => {
            try {
                const response = await fetch(`${endpoints[type]}?q=${encodeURIComponent(q)}`);
                if (!response.ok) throw new Error('Network');
                items = await response.json();
            } catch {
                items = [];
            }
            render();
        }, 250);
    });

    input.addEventListener('keydown', (e) => {
        if (list.hidden) {
            return;
        }
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                index = Math.min(index + 1, list.children.length - 1);
                highlight();
                break;
            case 'ArrowUp':
                e.preventDefault();
                index = Math.max(index - 1, 0);
                highlight();
                break;
            case 'Enter':
                if (index >= 0) {
                    e.preventDefault();
                    select(list.children[index]);
                }
                break;
            case 'Escape':
                list.hidden = true;
                break;
            default:
        }
    });

    list.addEventListener('mousedown', (e) => {
        if (e.target.tagName === 'LI') {
            select(e.target);
        }
    });

    document.addEventListener('click', (e) => {
        if (!list.contains(e.target) && e.target !== input) {
            list.hidden = true;
        }
    });

    function render() {
        list.innerHTML = '';
        index = -1;
        if (items.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'No matches';
            list.appendChild(li);
        } else {
            items.forEach((item) => {
                const li = document.createElement('li');
                li.textContent = item.name;
                li.dataset.slug = item.slug;
                list.appendChild(li);
            });
        }
        const rect = input.getBoundingClientRect();
        list.style.left = rect.left + window.scrollX + 'px';
        list.style.top = rect.bottom + window.scrollY + 'px';
        list.style.width = rect.width + 'px';
        list.hidden = false;
    }

    function highlight() {
        Array.from(list.children).forEach((li, i) => {
            li.classList.toggle('selected', i === index);
        });
    }

    function select(li) {
        if (li.dataset.slug) {
            input.value = li.textContent;
            hidden.value = li.dataset.slug;
        }
        list.hidden = true;
    }
}

window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-autocomplete]').forEach(initAutocomplete);
});
