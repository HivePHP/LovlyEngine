export default class PasswordStrength {
    constructor(input, wrapper) {
        this.input = input;
        this.wrapper = wrapper;
        this.bar = wrapper.querySelector('.strength-bar');
        this.text = wrapper.querySelector('.strength-text');

        this.input.addEventListener('input', () => this.update());
    }

    update() {
        const v = this.input.value.trim();

        if (!v) {
            this.wrapper.style.display = 'none';
            this.bar.style.width = '0%';
            return;
        }

        this.wrapper.style.display = 'block';

        let score = 0;
        if (v.length >= 1) score++;
        if (v.length >= 10) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;

        this.bar.style.width = (score / 5 * 100) + '%';
        this.text.textContent = `Сложность: ${score}/5`;
    }
}
