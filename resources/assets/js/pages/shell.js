import Dom from '../core/Dom.js';
import Dropdown from '../core/Dropdown.js';
import ConfirmSubmit from '../core/ConfirmSubmit.js';
import LoginForm from '../forms/LoginForm.js';

document.addEventListener('DOMContentLoaded', () => {
    new Dropdown(
        Dom.qs('#userDropdownToggle'),
        Dom.qs('#userDropdown')
    );

    const confirmForm = Dom.qs('form[data-confirm]');
    new ConfirmSubmit(confirmForm, confirmForm?.dataset.confirm);

    new LoginForm(Dom.qs('#login-form-compact'));
});
