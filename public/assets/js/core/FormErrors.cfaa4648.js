export default class FormErrors {
    static show(el, msg) {
        this.clear(el);
        el.classList.add('input-error');

        const err = document.createElement('div');
        err.className = 'error-text';
        err.textContent = msg;

        const group = el.closest('.form-group');
        if (group) {
            group.appendChild(err);
        } else {
            el.insertAdjacentElement('afterend', err);
        }
        return false;
    }

    static clear(el) {
        el.classList.remove('input-error');

        const group = el.closest('.form-group');
        const err = group
            ? group.querySelector('.error-text')
            : el.nextElementSibling;

        if (err && err.classList.contains('error-text')) {
            err.remove();
        }
    }
}
