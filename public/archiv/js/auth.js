/* ============================
   Form Helper
============================ */
class FormHelper {
    static $id(id) { return document.getElementById(id); }
    static _errorTimers = {};

    static showErrorGeneral(id, msg) {
        const err = this.$id('err_' + id);
        if (!err) return;

        err.textContent = msg;
        err.classList.add('show');

        // Если для этого блока уже есть таймер — сбрасываем
        if (this._errorTimers[id]) {
            clearTimeout(this._errorTimers[id]);
        }

        // Авто-скрытие через 3 секунды
        this._errorTimers[id] = setTimeout(() => {
            err.classList.remove('show');
        }, 3000);
    }
    static showError(id, msg) {
        const input = this.$id(id);
        const err = this.$id('err_' + id);

        input?.classList.add('input-error');
        input?.classList.remove('input-success');

        if (err) {
            err.textContent = msg;
            err.classList.add('show');
        }
    }

    static clearError(id) {
        const input = this.$id(id);
        const err = this.$id('err_' + id);

        input?.classList.remove('input-error');

        if (err) {
            err.classList.remove('show');
            err.textContent = '';
        }
    }

    static markSuccess(id) {
        const el = this.$id(id);
        if (!el) return;
        el.classList.remove('input-error');
        el.classList.add('input-success');
    }

    static attachAutoClear(id) {
        const el = this.$id(id);
        if (!el) return;

        const err = this.$id('err_' + id);
        const clear = () => {
            err?.classList.remove('show');
            el.classList.remove('input-error');
            el.value.trim()
                ? el.classList.add('input-success')
                : el.classList.remove('input-success');
        };

        el.addEventListener('input', clear);
        el.addEventListener('change', clear);
    }
}

/* ============================
   Validator
============================ */
class Validator {
    static name(str) {
        return /^[_'\- a-zA-Z0-9а-яА-ЯёЁіІїЇ]+$/.test(str || '');
    }

    static email(str) {
        return /^[\w.+-]+@[\w-]+\.[a-z]{2,}$/i.test(str || '');
    }

    static passwordStrength(pass) {
        let score = 0;
        if (!pass) return { score, text: 'Очень слабый' };

        if (pass.length >= 6) score++;
        if (pass.length >= 10) score++;
        if (/[0-9]/.test(pass)) score++;
        if (/[A-ZА-Я]/.test(pass)) score++;
        if (/[^A-Za-z0-9]/.test(pass)) score++;

        const labels = ['Очень слабый', 'Очень слабый', 'Слабый', 'Средний', 'Хороший', 'Отличный'];
        return { score, text: labels[score] };
    }
}

/* ============================
   Register Form
============================ */
class RegisterForm {
    constructor(selector, opts = {}) {
        this.form = document.querySelector(selector);
        this.endpoint = opts.endpoint || '/register';

        this.fields = {
            name: 'name', surname: 'surname', sex: 'sex',
            day: 'day', month: 'month', year: 'year',
            country: 'country', city: 'select_city',
            email: 'email', pass1: 'new_pass', pass2: 'new_pass2'
        };

        this.strength = {
            wrap: 'password-strength',
            fill: 'strength-fill',
            text: 'strength-text'
        };
    }

    init() {
        if (!this.form) return;
        this.fillDateInputs();
        this.attachListeners();
    }

    fillDateInputs() {
        const d = FormHelper.$id('day');
        const m = FormHelper.$id('month');
        const y = FormHelper.$id('year');

        if (!d || !m || !y) return;

        d.innerHTML = '<option value="">День</option>' +
            [...Array(31)].map((_, i) => `<option>${i + 1}</option>`).join('');

        m.innerHTML = '<option value="">Месяц</option>' +
            [...Array(12)].map((_, i) => `<option>${i + 1}</option>`).join('');

        const cur = new Date().getFullYear();
        y.innerHTML = '<option value="">Год</option>' +
            [...Array(101)].map((_, i) => `<option>${cur - i}</option>`).join('');
    }

    attachListeners() {
        Object.values(this.fields).forEach(id => FormHelper.attachAutoClear(id));

        const pw = FormHelper.$id(this.fields.pass1);
        pw?.addEventListener('input', () => this.updateStrength());

        this.form.addEventListener('submit', e => {
            e.preventDefault();
            this.submit();
        });
    }

    updateStrength() {
        const pass = FormHelper.$id(this.fields.pass1)?.value || '';
        const wrap = FormHelper.$id(this.strength.wrap);
        const fill = FormHelper.$id(this.strength.fill);
        const text = FormHelper.$id(this.strength.text);
        if (!wrap || !fill || !text) return;

        const res = Validator.passwordStrength(pass);
        wrap.style.display = 'block';

        const percent = res.score * 20;
        fill.style.width = percent + '%';
        fill.style.background = percent < 40 ? 'red' :
            percent < 60 ? 'orange' :
                percent < 80 ? 'yellowgreen' : 'green';

        text.textContent = res.text;
    }

    validate() {
        let ok = true;

        const required = ['name','surname','sex','day','month','year','country','select_city','email','new_pass','new_pass2'];
        required.forEach(id => {
            const el = FormHelper.$id(id);
            if (!el?.value.trim()) {
                FormHelper.showError(id, 'Поле не должно быть пустым');
                ok = false;
            } else FormHelper.markSuccess(id);
        });

        // name
        if (!Validator.name(FormHelper.$id('name').value)) {
            FormHelper.showError('name', 'Недопустимые символы');
            ok = false;
        }

        if (!Validator.name(FormHelper.$id('surname').value)) {
            FormHelper.showError('surname', 'Недопустимые символы');
            ok = false;
        }

        // email
        if (!Validator.email(FormHelper.$id('email').value)) {
            FormHelper.showError('email', 'Некорректный Email');
            ok = false;
        }

        // passwords
        const p1 = FormHelper.$id('new_pass').value;
        const p2 = FormHelper.$id('new_pass2').value;

        if (p1.length < 6) { FormHelper.showError('new_pass', 'Минимум 6 символов'); ok = false; }
        if (p1 !== p2) { FormHelper.showError('new_pass2', 'Пароли не совпадают'); ok = false; }

        // date
        const d = +FormHelper.$id('day').value;
        const m = +FormHelper.$id('month').value;
        const y = +FormHelper.$id('year').value;
        const valid = new Date(y, m - 1, d);
        if (valid.getDate() !== d) {
            const err = FormHelper.$id('err_birthday');
            err.textContent = 'Некорректная дата';
            err.classList.add('show');
            ok = false;
        }

        return ok;
    }

    async submit() {
        if (!this.validate()) return;

        const body = {
            name: FormHelper.$id('name').value.trim(),
            surname: FormHelper.$id('surname').value.trim(),
            sex: FormHelper.$id('sex').value,
            day: FormHelper.$id('day').value,
            month: FormHelper.$id('month').value,
            year: FormHelper.$id('year').value,
            country: FormHelper.$id('country').value.trim(),
            city: FormHelper.$id('select_city').value.trim(),
            email: FormHelper.$id('email').value.trim(),
            password_first: FormHelper.$id('new_pass').value,
            password_second: FormHelper.$id('new_pass2').value
        };

        try {
            const resp = await fetch(this.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const text = await resp.text();
            let data;

            try { data = JSON.parse(text); }
            catch {
                console.error("RAW RESPONSE:", text);
                alert("Сервер вернул не JSON");
                return;
            }

            if (data.status === 'ok') {
                location.href = '/id' + data.uid;
                return;
            }

            if (data.errors) {
                Object.entries(data.errors).forEach(([field, msg]) => {
                    FormHelper.showError(field === 'city' ? 'select_city' : field, msg);
                });
            }

        } catch (e) {
            console.error(e);
            alert("Ошибка сети");
        }
    }
}

/* ============================
   Login Form
============================ */
class LoginForm {
    constructor(selector, opts = {}) {
        this.form = document.querySelector(selector);
        this.endpoint = opts.endpoint || '/login';
    }

    init() {
        if (!this.form) return;
        FormHelper.attachAutoClear('login_email');
        FormHelper.attachAutoClear('login_pass');

        this.form.addEventListener('submit', e => {
            e.preventDefault();
            this.submit();
        });
    }

    async submit() {
        FormHelper.clearError('login_email');
        FormHelper.clearError('login_pass');

        const email = FormHelper.$id('login_email').value.trim();
        const pass = FormHelper.$id('login_pass').value;
        const remember = FormHelper.$id('remember_me')?.checked || false;

        if (!Validator.email(email)) {
            FormHelper.showError('login_email', 'Некорректный email');
            return;
        }

        if (!pass) {
            FormHelper.showError('login_pass', 'Введите пароль');
            return;
        }

        try {
            const resp = await fetch(this.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password: pass, remember })
            });

            const data = await resp.json();

            if (data.status === 'ok') {
                location.href = data.redirect || '/';
                return;
            }

            if (data.errors) {
                if (data.errors.email) FormHelper.showError('login_email', data.errors.email);
                if (data.errors.password) FormHelper.showError('login_pass', data.errors.password);
                if (data.errors.general) FormHelper.showErrorGeneral('auth', data.errors.general);
            }

        } catch (e) {
            console.error(e);
            alert("Ошибка подключения");
        }
    }
}
