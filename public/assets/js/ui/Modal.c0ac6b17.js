import Dom from '../core/Dom.3c5c29e1.js';

/**
 * Modal — универсальное модульное модальное окно.
 *
 * Разметка в Twig:
 *   <div class="modal" data-modal hidden>
 *       <div class="modal-backdrop" data-modal-close></div>
 *       <div class="modal-card">
 *           <h3 class="modal-title" data-modal-title>Заголовок</h3>
 *           <div class="modal-body" data-modal-body>
 *               ... содержимое окна ...
 *           </div>
 *           <div class="modal-error" data-modal-error></div>
 *           <div class="modal-actions" data-modal-actions>
 *               ... кнопки (закрывающие - с data-modal-close) ...
 *           </div>
 *       </div>
 *   </div>
 *
 * Возможности:
 *   - открытие/закрытие (open/close/toggle)
 *   - закрытие по клику на backdrop и любые элементы с [data-modal-close]
 *   - закрытие по Escape
 *   - установка заголовка setTitle() и ошибки setError()
 *   - хуки onOpen / onClose
 *   - визуально-скрытые (hidden) окна не ловят Escape
 */
export default class Modal {
    /**
     * @param {Element|string} root — элемент .modal (data-modal) или селектор
     */
    constructor(root) {
        this.root = (typeof root === 'string') ? Dom.qs(root) : root;
        if (!this.root) return;

        this.title = Dom.qs('[data-modal-title]', this.root);
        this.error = Dom.qs('[data-modal-error]', this.root);

        this._onOpen = null;
        this._onClose = null;

        this._onKeydown = (e) => {
            if (e.key === 'Escape' && !this.root.hidden) {
                e.preventDefault();
                this.close();
            }
        };
        this._onBackdrop = (e) => {
            if (e.target === e.currentTarget) this.close();
        };

        Dom.qsa('[data-modal-close]', this.root).forEach((el) => {
            el.addEventListener('click', () => this.close());
        });
        Dom.qs('[data-modal-backdrop]', this.root)?.addEventListener('click', this._onBackdrop);

        document.addEventListener('keydown', this._onKeydown);
    }

    /** Хук, вызывается после открытия окна. */
    set onOpen(fn) { this._onOpen = fn; }
    /** Хук, вызывается после закрытия окна. */
    set onClose(fn) { this._onClose = fn; }

    get isOpen() {
        return !!(this.root && !this.root.hidden);
    }

    open() {
        if (!this.root || this.isOpen) return;
        this.root.hidden = false;
        if (typeof this._onOpen === 'function') this._onOpen();
    }

    close() {
        if (!this.root || this.root.hidden) return;
        this.root.hidden = true;
        if (typeof this._onClose === 'function') this._onClose();
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    setTitle(text) {
        if (this.title) this.title.textContent = text;
    }

    setError(message) {
        if (this.error) this.error.textContent = message || '';
    }

    clearError() {
        this.setError('');
    }

    destroy() {
        if (this.root) {
            Dom.qs('[data-modal-backdrop]', this.root)?.removeEventListener('click', this._onBackdrop);
        }
        document.removeEventListener('keydown', this._onKeydown);
    }
}
