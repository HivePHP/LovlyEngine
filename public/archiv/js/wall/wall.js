class Wall {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.input = wrapper.querySelector('.wall-input');

        // обёртка и реальный textarea
        this.textareaWrap = wrapper.querySelector('.wall-textarea-wrap');
        this.textarea = wrapper.querySelector('.wall-textarea');

        this.init();
    }

    init() {
        // Клик на input → показать textarea
        this.input.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showTextarea();
        });

        // Клик на textarea → НЕ скрывать
        this.textareaWrap.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Авто-увеличение
        this.textarea.addEventListener('input', () => this.autoResize());

        // Клик вне wall-block → скрыть textarea
        document.addEventListener('click', () => {
            this.hideTextarea();
        });

        // если нужно — клик по emoji (пример: открыть эмоджи-панель)
        const emojiBtn = this.wrapper.querySelector('.emoji-btn');
        if (emojiBtn) {
            emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // чтобы клик по кнопке не сработал как клик вне
                // TODO: показать панель эмодзи. Для примера вставляем смайл в курсор:
                this.insertAtCursor('😊');
                this.textarea.focus();
            });
        }
    }

    showTextarea() {
        // показываем wrap и сам textarea
        this.textareaWrap.style.display = 'block';
        this.input.style.display = 'none';

        // переносим текст из input в textarea
        this.textarea.value = this.input.value || '';

        // корректная высота до фокуса
        this.autoResize();

        // фокус и установка курсора в конец
        this.textarea.focus();
        // Устанавливаем каретку в конец (надёжно)
        const len = this.textarea.value.length;
        this.textarea.setSelectionRange(len, len);
    }

    hideTextarea() {
        // переносим текст назад в input
        this.input.value = this.textarea.value || '';

        // сбрасываем высоту для следующего показа
        this.textarea.style.height = '';
        this.textareaWrap.style.display = 'none';
        this.input.style.display = 'block';
    }

    autoResize() {
        // авто-рост
        this.textarea.style.height = 'auto';
        this.textarea.style.height = (this.textarea.scrollHeight) + 'px';
    }

    insertAtCursor(text) {
        // вставка текста в текущую позицию курсора в textarea
        const ta = this.textarea;
        const start = ta.selectionStart || 0;
        const end = ta.selectionEnd || 0;
        const value = ta.value || '';
        ta.value = value.slice(0, start) + text + value.slice(end);
        // перемещаем курсор сразу после вставленного текста
        const pos = start + text.length;
        ta.setSelectionRange(pos, pos);
        this.autoResize();
    }
}

// Инициализация (если на странице несколько таких блоков — можно итерировать)
document.querySelectorAll('.wall-block').forEach(el => new Wall(el));