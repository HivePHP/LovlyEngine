import Dom from '../core/Dom.3c5c29e1.js';
import Validator from '../core/Validator.a12b4651.js';
import FormErrors from '../core/FormErrors.cfaa4648.js';

export default class RegistrationValidator {

    stepOne() {
        const n = Dom.qs('[name="name"]');
        const s = Dom.qs('[name="surname"]');

        if (!Validator.name(n.value)) return FormErrors.show(n, 'Некорректное имя');
        if (!Validator.name(s.value)) return FormErrors.show(s, 'Некорректная фамилия');
        return true;
    }

    stepTwo() {
        for (const f of ['day','month','year']) {
            const el = Dom.qs(`[name="${f}"]`);
            if (!el.value) return FormErrors.show(el, 'Обязательное поле');
        }
        return true;
    }

    stepThree() {
        const sex = Dom.qs('[name="sex"]');
        const city = Dom.qs('[name="city"]');
        const country = Dom.qs('[name="country"]');

        if (!sex.value) return FormErrors.show(sex, 'Выберите пол');
        if (!city.value) return FormErrors.show(city, 'Введите город');
        if (!country.value) return FormErrors.show(country, 'Введите страну');
        return true;
    }

    stepFour() {
        const email = Dom.qs('[name="email"]');
        const p1 = Dom.qs('[name="password1"]');
        const p2 = Dom.qs('[name="password2"]');

        if (!Validator.email(email.value)) return FormErrors.show(email, 'Email неверный');
        if (!Validator.minLength(p1.value, 8)) return FormErrors.show(p1, 'Мин 8 символов');
        if (p1.value !== p2.value) return FormErrors.show(p2, 'Пароли не совпадают');
        return true;
    }

    stepFive() {
        const el = Dom.qs('[name="captcha"]');

        if (!el.value) return FormErrors.show(el, 'Введите код с картинки');
        return true;
    }
}
