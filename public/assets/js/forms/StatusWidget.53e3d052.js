import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import Modal from '../ui/Modal.c0ac6b17.js';

export default class StatusWidget extends Modal {
    constructor(
        cls = '[data-status-widget]',
        modal = '[data-status-modal]'
    ) {
        const widget = Dom.qs(cls);
        const modalRoot = Dom.qs(modal);
        super(modalRoot);

        if (!widget || !modalRoot || widget.dataset.statusBound === '1') {
            return;
        }

        this.widget = widget;
        this.widget.dataset.statusBound = '1';

        this.input = Dom.qs('[name="status"]', modalRoot);
        this.toggle = Dom.qs('[data-status-toggle]', widget);

        if (this.toggle) {
            this.toggle.addEventListener('click', () => this.open());
        }

        this.onOpen = () => {
            this.input.value = this.toggle?.dataset.value ?? this.widget.dataset.currentStatus ?? '';
            this.clearError();
            this.input.focus();
        };

        this.onClose = () => this.clearError();

        Dom.qs('[data-status-save]', modalRoot)?.addEventListener('click', () => this.save());
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
            btn.textContent = value;
            btn.title = 'Нажмите, чтобы изменить статус';

            this.widget.innerHTML = '';
            this.widget.appendChild(btn);
        }

        this.toggle = Dom.qs('[data-status-toggle]', this.widget);
        this.widget.dataset.currentStatus = value;
        this.toggle.addEventListener('click', () => this.open());
    }
}
