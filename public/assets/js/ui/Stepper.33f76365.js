export default class Stepper {
    constructor(steps, onFinish) {
        this.steps = steps;
        this.current = 0;
        this.onFinish = onFinish;
    }

    next() {
        if (this.current < this.steps.length - 1) {
            this.current++;
            this.render();
        } else {
            this.onFinish();
        }
    }

    prev() {
        if (this.current > 0) {
            this.current--;
            this.render();
        }
    }

    render() {
        this.steps.forEach((s, i) => {
            s.style.display = i === this.current ? 'block' : 'none';
        });

        const fill = document.getElementById('progressFill');
        if (fill) {
            fill.style.width = ((this.current + 1) / this.steps.length * 100) + '%';
        }
    }
}
