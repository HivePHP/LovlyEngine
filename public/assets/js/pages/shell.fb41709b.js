import Dom from '../core/Dom.3c5c29e1.js';
import Dropdown from '../core/Dropdown.ec98bfa8.js';
import ConfirmSubmit from '../core/ConfirmSubmit.61b92cdc.js';
import LoginForm from '../forms/LoginForm.e00bd1ab.js';
import NotificationBell from '../core/NotificationBell.ad8e673c.js';

document.addEventListener('DOMContentLoaded', () => {
    new Dropdown(
        Dom.qs('#userDropdownToggle'),
        Dom.qs('#userDropdown')
    );

    const confirmForm = Dom.qs('form[data-confirm]');
    new ConfirmSubmit(confirmForm, confirmForm?.dataset.confirm);

    new LoginForm(Dom.qs('#login-form-compact'));

    new NotificationBell(Dom.qs('[data-notifications-root]'));
});
