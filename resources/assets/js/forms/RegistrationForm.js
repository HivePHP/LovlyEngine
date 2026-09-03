import Dom from '../core/Dom.js';
import FormErrors from '../core/FormErrors.js';
import Ajax from '../core/Ajax.js';
import Stepper from '../ui/Stepper.js';
import PasswordStrength from '../ui/PasswordStrength.js';
import BirthdateSelect from '../ui/BirthdateSelect.js';
import RegistrationValidator from './RegistrationValidator.js';

export default class RegistrationForm {
    constructor() {
        this.validator = new RegistrationValidator();

        this.stepper = new Stepper([
            Dom.qs('#step-one'),
            Dom.qs('#step-two'),
            Dom.qs('#step-three'),
            Dom.qs('#step-four'),
            Dom.qs('#step-five')
        ], () => this.submit());

        new BirthdateSelect(
            Dom.qs('[name="day"]'),
            Dom.qs('[name="month"]'),
            Dom.qs('[name="year"]')
        );

        new PasswordStrength(
            Dom.qs('[name="password1"]'),
            Dom.qs('.password-strength')
        );

        this.btnNext = Dom.qs('.btn-primary-reg');
        this.btnBack = this.createBackButton();
        this.captchaImage = Dom.qs('#captcha-image');

        this.bind();
        this.stepper.render();
    }

    createBackButton() {
        const btn = document.createElement('button');
        btn.className = 'btn-back';
        btn.textContent = 'Назад';
        btn.style.display = 'none';

        Dom.qs('.reg-card')?.prepend(btn);
        return btn;
    }

    bind() {
        this.btnNext.onclick = () => this.next();
        this.btnBack.onclick = () => this.stepper.prev();

        const refresh = Dom.qs('#captcha-refresh');
        if (refresh) refresh.onclick = () => this.refreshCaptcha();

        Dom.qsa('input, select').forEach(el => {
            el.addEventListener('input', () => FormErrors.clear(el));
        });
    }

    refreshCaptcha() {
        if (!this.captchaImage) return;

        const input = Dom.qs('[name="captcha"]');

        // Clear any stale value so it can't be silently re-submitted while the
        // shown code is already a different one than the previously typed text.
        if (input) input.value = '';

        this.captchaImage.src = '/captcha/image?' + Date.now();
    }

    focusCaptcha() {
        const input = Dom.qs('[name="captcha"]');
        if (!input) return;

        input.focus();
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    next() {
        const map = [
            () => this.validator.stepOne(),
            () => this.validator.stepTwo(),
            () => this.validator.stepThree(),
            () => this.validator.stepFour(),
            () => this.validator.stepFive()
        ];

        if (!map[this.stepper.current]()) return;
        this.stepper.next();
    }

    async submit() {
        const data = Object.fromEntries(
            Dom.qsa('[name]').map(el => [el.name, el.value])
        );

        try {
            const res = await Ajax.post('/register', data);
            if (res.status === 'validation_error') {
                for (const [f, m] of Object.entries(res.errors)) {
                    const el = Dom.qs(`[name="${f}"]`);
                    if (el) FormErrors.show(el, m);
                    if (f === 'captcha') {
                        this.refreshCaptcha();
                        this.focusCaptcha();
                    }
                }
            }
            if (res.status === 'ok') location.href = '/id' + res.uid;
        } catch (err) {
            window.alert('Ошибка соединения (см. консоль)');
            console.error(err);
        }
    }
}
