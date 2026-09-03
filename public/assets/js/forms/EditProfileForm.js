import Dom from '../core/Dom.js';
import FormErrors from '../core/FormErrors.js';
import Ajax from '../core/Ajax.js';

export default class EditProfileForm {
    constructor(form, options = {}) {
        this.form = form;
        this.options = {
            successBox: null,
            saveBtn: null,
            busyText: 'Сохранение...',
            idleText: 'Сохранить',
            successText: 'Изменения сохранены!',
            successTimeout: 3000,
            ...options,
        };

        if (!this.form || this.form.dataset.editBound === '1') {
            return;
        }

        this.form.dataset.editBound = '1';
        this.bind();
    }

    bind() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();

        const data = {};
        for (const el of Dom.qsa('[name]', this.form)) {
            data[el.name] = el.value;
        }

        this.setBusy(true);

        try {
            const res = await Ajax.post('/api/profile/update', data);
            if (res.status === 'ok') {
                this.showSuccess();
            } else if (res.status === 'validation_error' && res.errors) {
                this.renderErrors(res.errors);
            } else {
                this.showError('Ошибка сохранения');
            }
        } catch (err) {
            this.showError('Ошибка сети');
        } finally {
            this.setBusy(false);
        }
    }

    renderErrors(errors) {
        for (const [field, message] of Object.entries(errors)) {
            const el = Dom.qs(`[name="${field}"]`, this.form);
            if (el) FormErrors.show(el, message);
        }
    }

    showSuccess() {
        if (this.options.successBox) {
            this.options.successBox.textContent = this.options.successText;
            this.options.successBox.style.display = 'block';

            clearTimeout(this._successTimer);
            this._successTimer = setTimeout(() => {
                this.options.successBox.style.display = 'none';
            }, this.options.successTimeout);
        }
    }

    showError(message) {
        window.alert(message);
    }

    setBusy(busy) {
        const btn = this.options.saveBtn ?? Dom.qs('button[type="submit"]', this.form);
        if (!btn) return;

        btn.disabled = busy;
        btn.textContent = busy ? this.options.busyText : this.options.idleText;
    }
}
