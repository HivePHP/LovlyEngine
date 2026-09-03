import Dom from '../core/Dom.3c5c29e1.js';
import LoginForm from '../forms/LoginForm.e00bd1ab.js';

export function init() {
    new LoginForm(Dom.qs('#login-form'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
