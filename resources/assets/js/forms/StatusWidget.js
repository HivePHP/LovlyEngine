import Dom from '../core/Dom.js';
import Ajax from '../core/Ajax.js';

export default class StatusWidget {
    constructor(cls = '[data-status-widget]', modal = '[data-status-modal]') {
        this.widget = Dom.qs(cls);
        this.modal = Dom.qs(modal);

        if (!this.widget || !this.modal || this.widget.dataset.statusBound === '1') {
            return;
        }

        this.widget.dataset.statusBound = '1';

        this.input = Dom.qs('[name="status"]', this.modal);
        this.error = Dom.qs('[data-status-error]', this.modal);
        this.toggle = Dom.qs('[data-status-toggle]', this.widget);

        this.bind();
    }

    bind() {
        if (this.toggle) {
            this.toggle.addEventListener('click', () => this.open());
        }

        Dom.qs('[data-status-close]', this.modal)?.addEventListener('click', () => this.close());
        Dom.qs('[data-status-save]', this.modal)?.addEventListener('click', () => this.save());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.hidden) this.close();
        });
    }

    open() {
        this.input.value = this.toggle?.dataset.value ?? this.widget.dataset.currentStatus ?? '';
        this.setError('');
        this.modal.hidden = false;
        this.input.focus();
    }

    close() {
        this.modal.hidden = true;
        this.setError('');
    }

    setError(message) {
        this.error.textContent = message;
    }

    async save() {
        const value = this.input.value.trim();

        if (value.length > 120) {
            this.setError('Статус слишком длинный (макс. 120 символов)');
            return;
        }

        try {
            const res = await Ajax.post('/api/profile/status', { status: value });

            if (res.status === 'validation_error' && res.errors) {
                this.setError(Object.values(res.errors).join(' '));
                return;
            }

            if (res.status === 'ok') {
                this.applyResult(res.value ?? '');
                this.close();
            }
        } catch (err) {
            this.setError('Ошибка сети. Попробуйте ещё раз.');
        }
    }

    applyResult(value) {
        if (value === '') {
            this.widget.innerHTML =
                '<button type="button" class="profile-status-set" data-status-toggle>Установить статус</button>';
        } else {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'profile-status-text';
            btn.dataset.statusToggle = '';
            btn.dataset.value = value;
            btn.title = 'Нажмите, чтобы изменить статус';
            btn.textContent = value;

            this.widget.innerHTML = '';
            this.widget.appendChild(btn);
        }

        this.toggle = Dom.qs('[data-status-toggle]', this.widget);
        this.widget.dataset.currentStatus = value;

        this.toggle.addEventListener('click', () => this.open());
    }
}
