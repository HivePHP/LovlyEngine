import RegistrationForm from '../forms/RegistrationForm.47477cf5.js';

export function init() {
    new RegistrationForm();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
