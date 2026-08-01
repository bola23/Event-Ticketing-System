import Alpine from 'alpinejs';
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import 'intl-tel-input/styles';

window.Alpine = Alpine;

Alpine.store('ticketRequest', {
    open: false,
    ticketTypeId: null,
    show(ticketTypeId) {
        this.ticketTypeId = ticketTypeId;
        this.open = true;
    },
});

Alpine.start();

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        Alpine.store('ticketRequest').open = false;
    }
});

const phoneInput = document.querySelector('#phone');
if (phoneInput) {
    const iti = intlTelInput(phoneInput, {
        initialCountry: 'eg',
    });

    phoneInput.closest('form')?.addEventListener('submit', () => {
        phoneInput.value = iti.getNumber();
    });
}

function filterInputCharacters(inputEl, disallowedPattern) {
    inputEl.addEventListener('input', () => {
        const cursorPos = inputEl.selectionStart;
        const originalLength = inputEl.value.length;
        inputEl.value = inputEl.value.replace(disallowedPattern, '');
        const removed = originalLength - inputEl.value.length;
        const newCursorPos = Math.max(0, cursorPos - removed);
        inputEl.setSelectionRange(newCursorPos, newCursorPos);
    });
}

const nameInput = document.querySelector('#name');
if (nameInput) {
    // Mirrors the server-side regex: unicode letters/marks, spaces, apostrophe, hyphen, period only.
    filterInputCharacters(nameInput, /[^\p{L}\p{M}\s'\-.]/gu);
}

const emailInput = document.querySelector('#email');
if (emailInput) {
    filterInputCharacters(emailInput, /[^a-zA-Z0-9@._%+\-]/g);
}

const ticketRequestForm = document.getElementById('ticket-request-form');
if (ticketRequestForm) {
    ticketRequestForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const feedback = document.getElementById('ticket-request-feedback');
        const submitButton = ticketRequestForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        const genericError = ticketRequestForm.dataset.genericError;

        ticketRequestForm.querySelectorAll('[id^="error-"]').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        if (feedback) {
            feedback.textContent = '';
            feedback.classList.add('hidden');
        }

        submitButton.disabled = true;
        submitButton.textContent = '...';

        try {
            const response = await fetch(ticketRequestForm.action, {
                method: 'POST',
                body: new FormData(ticketRequestForm),
                headers: { Accept: 'application/json' },
            });

            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                Object.entries(data.errors ?? {}).forEach(([field, messages]) => {
                    const errorEl = document.getElementById('error-' + field);
                    if (errorEl) {
                        errorEl.textContent = messages[0];
                        errorEl.classList.remove('hidden');
                    }
                });
            } else if (response.ok) {
                if (feedback) {
                    feedback.textContent = data.message || '';
                    feedback.className = 'text-sm font-bold mb-4 text-ccs-teal-light';
                }
                ticketRequestForm.reset();
            } else {
                if (feedback) {
                    feedback.textContent = data.message || genericError;
                    feedback.className = 'text-sm font-bold mb-4 text-red-400';
                }
            }
        } catch (error) {
            if (feedback) {
                feedback.textContent = genericError;
                feedback.className = 'text-sm font-bold mb-4 text-red-400';
            }
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        }
    });
}

if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

    document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
} else {
    document.querySelectorAll('[data-reveal]').forEach((el) => el.classList.add('is-visible'));
}
