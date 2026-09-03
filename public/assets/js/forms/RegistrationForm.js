import Dom from '../core/Dom.js';
import FormErrors from '../core/FormErrors.js';
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
            Dom.qs('#step-four')
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

        Dom.qsa('input, select').forEach(el => {
            el.addEventListener('input', () => FormErrors.clear(el));
        });
    }

    next() {
        const map = [
            () => this.validator.stepOne(),
            () => this.validator.stepTwo(),
            () => this.validator.stepThree(),
            () => this.validator.stepFour()
        ];

        if (!map[this.stepper.current]()) return;
        this.stepper.next();
    }

    submit() {
        const data = Object.fromEntries(
            Dom.qsa('[name]').map(el => [el.name, el.value])
        );

        fetch('/register', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'validation_error') {
                    for (const [f,m] of Object.entries(res.errors)) {
                        const el = Dom.qs(`[name="${f}"]`);
                        if (el) FormErrors.show(el, m);
                    }
                }
                if (res.status === 'ok') location.href = '/id' + res.uid;
            });
    }
}
