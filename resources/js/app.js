import Alpine from 'alpinejs';
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import 'intl-tel-input/styles';

window.Alpine = Alpine;
Alpine.start();

const phoneInput = document.querySelector('#phone');
if (phoneInput) {
    const iti = intlTelInput(phoneInput, {
        initialCountry: 'eg',
    });

    phoneInput.closest('form')?.addEventListener('submit', () => {
        phoneInput.value = iti.getNumber();
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
