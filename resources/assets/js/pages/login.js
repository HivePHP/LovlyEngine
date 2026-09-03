import Dom from '../core/Dom.js';
import LoginForm from '../forms/LoginForm.js';

export function init() {
    new LoginForm(Dom.qs('#login-form'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
