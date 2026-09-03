import RegistrationForm from '../forms/RegistrationForm.js';

export function init() {
    new RegistrationForm();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
