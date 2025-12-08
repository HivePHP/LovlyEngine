class UserStatus {
    constructor(block) {
        this.block = block;
        this.text = document.getElementById('text-select');
        this.addStatusBg = block.querySelector('.status-add-bg');
        this.input = this.addStatusBg.querySelector('.profile-status-input');
        this.userId = block.dataset.userId;

        this.init();
    }

    init() {
        this.text.addEventListener('click', () => this.showInput());

        this.input.addEventListener('blur', () => this.save());
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.input.blur();
            }
        });
    }

    showInput() {
        this.addStatusBg.style.display = 'block';
        this.input.value = this.text.textContent.trim() === 'Установить статус' ? '' : this.text.textContent.trim();
        this.input.focus();
    }

    save() {
        const status = this.input.value.trim();

        fetch('/user/statusUpdate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${this.userId}&status=${encodeURIComponent(status)}`
        })
            .then(res => res.text())
            .then(text => {
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    console.error('Сервер вернул не JSON:', text);
                    return;
                }
                this.addStatusBg.style.display = 'none';
                this.text.style.display = 'inline';
                this.text.textContent = data.status || 'Установить статус';
            })
            .catch(err => {
                console.error('Ошибка при сохранении статуса', err);
                this.addStatusBg.style.display = 'none';
                this.text.style.display = 'inline';
            });
    }
}

// Инициализация
document.querySelectorAll('.profile-status-block').forEach(block => {
    new UserStatus(block);
});
