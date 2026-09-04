import Dom from '../core/Dom.js';
import Ajax from '../core/Ajax.js';

const ACTION_URL = {
    add:     (id) => `/api/friends/${id}/add`,
    accept:  (id) => `/api/friends/${id}/accept`,
    decline: (id) => `/api/friends/${id}/decline`,
    remove:  (id) => `/api/friends/${id}/remove`,
};

/* Rendering plans per relationship state. */
const PLANS = {
    none: [
        { action: 'add',   class: 'friend-btn--primary', label: 'Добавить в друзья' },
    ],
    outgoing: [
        { action: 'remove', class: '', label: 'Заявка отправлена' },
    ],
    incoming: [
        { action: 'accept',  class: 'friend-btn--primary', label: 'Принять заявку' },
        { action: 'decline', class: 'friend-btn--danger',   label: 'Отклонить' },
    ],
    friends: [
        { action: 'remove', class: 'friend-btn--primary', label: 'Удалить из друзей' },
    ],
};

export default class FriendButton {
    constructor(root) {
        this.root = root;
        if (!this.root) return;
        if (this.root.dataset.bound === '1') return;
        this.root.dataset.bound = '1';

        this.relation = this.root.dataset.relation || 'none';
        this.profileId = this.root.dataset.profileId;

        this.root.addEventListener('click', (e) => this.onClick(e));
        this.render();
    }

    setRelation(relation) {
        this.relation = relation;
        this.root.dataset.relation = relation;
        this.render();
    }

    render() {
        const links = Array.from(this.root.querySelectorAll('a'));
        const buttons = PLANS[this.relation] || PLANS.none;
        this.root.innerHTML = '';

        for (const plan of buttons) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'friend-btn friend-btn--profile' + (plan.class ? ' ' + plan.class : '');
            btn.textContent = plan.label;
            btn.dataset.friendAction = plan.action;
            this.root.appendChild(btn);
        }
        links.forEach((link) => this.root.appendChild(link));
    }

    onClick(e) {
        const btn = e.target.closest('[data-friend-action]');
        if (!btn) return;
        if (btn.disabled) return;

        const action = btn.dataset.friendAction;
        if (!ACTION_URL[action] || !this.profileId) return;

        // Disable briefly to avoid double-clicks.
        const buttons = Dom.qsa('[data-friend-action]', this.root);
        buttons.forEach((b) => (b.disabled = true));

        Ajax.post(ACTION_URL[action](this.profileId), {})
            .then((payload) => {
                if (payload.status !== 'ok') {
                    throw new Error(payload.message || 'Не удалось выполнить операцию.');
                }
                this.setRelation(payload.relation || 'none');
            })
            .catch((err) => {
                buttons.forEach((b) => (b.disabled = false));
            });
    }
}
