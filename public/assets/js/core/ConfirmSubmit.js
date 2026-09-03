export default class ConfirmSubmit {
    constructor(form, message = 'Вы уверены?') {
        this.form = form;
        this.message = message;

        if (!this.form || this.form.dataset.confirmBound === '1') {
            return;
        }

        this.form.dataset.confirmBound = '1';
        this.bind();
    }

    bind() {
        this.form.addEventListener('submit', (e) => {
            if (!window.confirm(this.message)) {
                e.preventDefault();
            }
        });
    }
}
