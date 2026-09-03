import Dom from '../core/Dom.3c5c29e1.js';
import FormErrors from '../core/FormErrors.cfaa4648.js';
import Ajax from '../core/Ajax.ce7e7771.js';

export default class LoginForm {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            redirectPath: null,
            errorBox: null,
            busyText: 'Входим...',
            idleText: 'Войти',
            ...options,
        };

        if (!this.form || this.form.dataset.loginBound === '1') {
            return;
        }

        this.form.dataset.loginBound = '1';
        this.bind();
    }

    bind() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();

        const payload = {
            email: this.getValue('email'),
            password: this.getPassword('password'),
            remember: this.isChecked('remember'),
        };

        this.setBusy(true);

        if (this.options.errorBox) {
            this.options.errorBox.style.display = 'none';
        }

        try {
            const res = await Ajax.post('/login', payload);
            await this.handleResponse(res);
        } catch (err) {
            this.showGlobal('Ошибка входа. Попробуйте ещё раз.');
        } finally {
            this.setBusy(false);
        }
    }

    async handleResponse(res) {
        if (res.status === 'ok' && res.uid) {
            window.location.href = this.options.redirectPath ?? '/id' + res.uid;
            return;
        }

        if (res.status === 'validation_error' && res.errors) {
            this.renderErrors(res.errors);
            return;
        }

        this.showGlobal('Неверный email или пароль');
    }

    renderErrors(errors) {
        let handled = false;

        for (const [field, message] of Object.entries(errors)) {
            const el = Dom.qs(`[name="${field}"]`, this.form);
            if (el) {
                FormErrors.show(el, message);
                handled = true;
            }
        }

        if (!handled && this.options.errorBox) {
            const msg = Object.values(errors)[0] || 'Неверные данные';
            this.showGlobal(msg);
        }
    }

    showGlobal(message) {
        if (this.options.errorBox) {
            this.options.errorBox.textContent = message;
            this.options.errorBox.style.display = 'block';
            return;
        }
        window.alert(message);
    }

    setBusy(busy) {
        const btn = Dom.qs('button[type="submit"]', this.form);
        if (!btn) return;

        btn.disabled = busy;
        btn.textContent = busy ? this.options.busyText : this.options.idleText;
    }

    getValue(name) {
        return Dom.qs(`[name="${name}"]`, this.form)?.value.trim() ?? '';
    }

    getPassword(name) {
        return Dom.qs(`[name="${name}"]`, this.form)?.value ?? '';
    }

    isChecked(name) {
        return Dom.qs(`[name="${name}"]`, this.form)?.checked ?? false;
    }
}
