class Modal {
    constructor(id) {
        this.modal = document.getElementById(id);
        this.body = document.body;
        this.init();
    }

    init() {
        this.modal.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        this.modal.addEventListener('mousedown', (e) => {
            if (e.target === this.modal) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.close();
        });
    }

    open() {
        // сохраняем текущий скролл
        const scrollY = window.scrollY;
        document.documentElement.style.setProperty('--scroll-y', `-${scrollY}px`);
        this.body.classList.add('modal-open');

        this.modal.classList.add('active');
    }

    close() {
        this.modal.classList.remove('active');

        const scrollY = this.body.style.top;
        this.body.classList.remove('modal-open');

        // возвращаем позицию прокрутки
        window.scrollTo(0, Math.abs(parseInt(scrollY || '0')));
    }
}

const myModal = new Modal('myModal');
document.getElementById('openModal').addEventListener('click', () => myModal.open());

