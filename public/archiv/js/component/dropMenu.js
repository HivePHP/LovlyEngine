class DropMenu {
    constructor(wrapper) {
        this.wrapper = wrapper;
        this.trigger = wrapper.querySelector('.avatar-trigger');
        this.menu = wrapper.querySelector('.dropdown-menu');

        this.trigger.addEventListener('click', () => this.toggle());
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) this.hide();
        });
    }

    toggle() {
        this.menu.classList.toggle('show');
    }

    hide() {
        this.menu.classList.remove('show');
    }
}
