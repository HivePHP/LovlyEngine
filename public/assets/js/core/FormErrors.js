export default class FormErrors {
    static show(el, msg) {
        this.clear(el);
        el.classList.add('input-error');

        const err = document.createElement('div');
        err.className = 'error-text';
        err.textContent = msg;

        el.closest('.form-group')?.appendChild(err);
        return false;
    }

    static clear(el) {
        el.classList.remove('input-error');
        el.closest('.form-group')?.querySelector('.error-text')?.remove();
    }
}
