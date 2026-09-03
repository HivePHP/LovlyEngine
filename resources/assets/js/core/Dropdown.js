export default class Dropdown {
    constructor(toggle, menu, options = {}) {
        this.toggle = toggle;
        this.menu = menu;
        this.openClass = options.openClass ?? 'open';
        this.onOpen = options.onOpen ?? null;
        this.onClose = options.onClose ?? null;

        if (!this.toggle || !this.menu) return;

        this.isOpen = false;
        this.bind();
        this._boundDocClick = (e) => this.handleDocClick(e);
        document.addEventListener('click', this._boundDocClick);
    }

    bind() {
        this.toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleOpen(!this.isOpen);
        });
    }

    toggleOpen(force) {
        this.isOpen = typeof force === 'boolean' ? force : !this.isOpen;
        this.menu.classList.toggle(this.openClass, this.isOpen);

        if (this.isOpen) {
            this.onOpen?.(this);
        } else {
            this.onClose?.(this);
        }
    }

    open() {
        this.toggleOpen(true);
    }

    close() {
        this.toggleOpen(false);
    }

    handleDocClick(e) {
        if (!this.menu.contains(e.target)) {
            this.close();
        }
    }

    destroy() {
        document.removeEventListener('click', this._boundDocClick);
    }
}
