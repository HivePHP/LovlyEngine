import Dom from '../core/Dom.js';
import LoginForm from '../forms/LoginForm.js';

document.addEventListener('DOMContentLoaded', () => {
    new LoginForm(Dom.qs('#login-form'));
});
