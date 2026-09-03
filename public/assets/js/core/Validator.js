export default class Validator {
    static name(val) {
        return /^[a-zA-Zа-яА-ЯёЁіІїЇ]+$/.test(val.trim());
    }

    static email(val) {
        return /^[\w.-]+@[\w.-]+\.\w{2,}$/i.test(val.trim());
    }

    static minLength(val, len) {
        return val.length >= len;
    }
}
