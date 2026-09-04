import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';

const ACTION_URL = {
    accept:  (id) => `/api/friends/${id}/accept`,
    decline: (id) => `/api/friends/${id}/decline`,
    remove:  (id) => `/api/friends/${id}/remove`,
    add:     (id) => `/api/friends/${id}/add`,
};

class FriendsPage {
    constructor(root) {
        this.root = root;
        if (this.root.dataset.bound === '1') return;
        this.root.dataset.bound = '1';

        this.tabs = Dom.qsa('[data-tab]', root);
        this.sections = Dom.qsa('[data-section]', root);
        this.searchInput = Dom.qs('[data-friend-search]', root);
        this.ctx = Dom.qs('[data-friends-ctx]', root);
        this.ctxTargetId = null;

        /* Tab clicks */
        this.tabs.forEach((tab) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.activate(tab.dataset.tab);
            });
        });

        /* Right sidebar tab links */
        Dom.qsa('[data-right-tab]', root).forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.activate(link.dataset.rightTab);
            });
        });

        /* Search filtering */
        this.searchInput?.addEventListener('input', () => this.filterFriends());

        /* Action buttons (accept/decline/remove) */
        root.addEventListener('click', (e) => this.onActionClick(e));

        /* Three-dot menu */
        root.addEventListener('click', (e) => this.onMenuClick(e));

        /* Context menu item clicks */
        this.ctx?.addEventListener('click', (e) => this.onCtxAction(e));

        /* Hide context menu on outside click */
        document.addEventListener('click', (e) => {
            if (this.ctx && !this.ctx.contains(e.target) && !e.target.closest('[data-friend-menu]')) {
                this.ctx.hidden = true;
            }
        });

        /* Possible friends "add" links */
        root.addEventListener('click', (e) => {
            const addBtn = e.target.closest('[data-add-friend]');
            if (!addBtn) return;
            e.preventDefault();
            const id = addBtn.dataset.addFriend;
            addBtn.textContent = 'Заявка отправлена';
            addBtn.style.color = '#818c96';
            addBtn.style.pointerEvents = 'none';
            Ajax.post(ACTION_URL.add(id), {});
        });
    }

    activate(name) {
        this.tabs.forEach((t) => {
            t.classList.toggle('is-active', t.dataset.tab === name);
        });
        this.sections.forEach((s) => {
            s.classList.toggle('is-active', s.dataset.section === name);
        });
        window.history.replaceState(null, '', '#' + name);
    }

    filterFriends() {
        const q = this.searchInput.value.toLowerCase().trim();
        Dom.qsa('.friend-row', this.root).forEach((row) => {
            const name = (row.dataset.name || '').toLowerCase();
            row.style.display = name.includes(q) ? '' : 'none';
        });
    }

    onMenuClick(e) {
        const menu = e.target.closest('[data-friend-menu]');
        if (!menu) return;
        e.preventDefault();
        e.stopPropagation();

        this.ctxTargetId = menu.dataset.friendMenu;
        this.ctx.dataset.name = menu.dataset.name || '';

        /* Set the "remove" label with friend name */
        const removeBtn = this.ctx.querySelector('[data-ctx-action="remove"]');
        if (removeBtn) {
            const name = menu.dataset.name || '';
            removeBtn.textContent = name ? `Удалить ${name} из друзей` : 'Удалить из друзей';
        }

        this.positionCtx(menu);
        this.ctx.hidden = false;
    }

    positionCtx(anchor) {
        const rect = anchor.getBoundingClientRect();
        let x = rect.left;
        let y = rect.bottom + 4;
        if (x + 200 > window.innerWidth) x = window.innerWidth - 210;
        if (y + 150 > window.innerHeight) y = rect.top - 150;
        this.ctx.style.left = x + 'px';
        this.ctx.style.top = y + 'px';
    }

    onCtxAction(e) {
        const btn = e.target.closest('[data-ctx-action]');
        if (!btn) return;
        e.preventDefault();

        const action = btn.dataset.ctxAction;
        const id = this.ctxTargetId;
        this.ctx.hidden = true;

        if (!id) return;

        if (action === 'message') {
            window.location.href = `/messages?to=${id}`;
        } else if (action === 'profile') {
            window.location.href = `/id${id}`;
        } else if (action === 'remove') {
            this.doAction('remove', id);
        }
    }

    onActionClick(e) {
        const btn = e.target.closest('[data-friend-action]');
        if (!btn) return;
        e.preventDefault();

        const action = btn.dataset.friendAction;
        const row = btn.closest('.friend-row');
        const id = row?.dataset.friendId;

        if (!ACTION_URL[action] || !id) return;

        this.doAction(action, id, row);
    }

    doAction(action, id, row) {
        if (!row) {
            row = this.root.querySelector(`.friend-row[data-friend-id="${id}"]`);
        }

        if (row) {
            Dom.qsa('[data-friend-action]', row).forEach((b) => (b.disabled = true));
        }

        Ajax.post(ACTION_URL[action](id), {})
            .then((payload) => {
                if (payload.status !== 'ok') {
                    throw new Error(payload.message || 'Не удалось выполнить операцию.');
                }
                if (row) {
                    row.style.transition = 'opacity 0.2s, transform 0.2s';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 220);
                }
                document.dispatchEvent(new CustomEvent('notifications:refresh'));
            })
            .catch((err) => {
                if (row) {
                    Dom.qsa('[data-friend-action]', row).forEach((b) => (b.disabled = false));
                }
            });
    }
}

/* PAGE INIT */
export function init() {
    const root = Dom.qs('[data-friends-page]');
    if (!root) return;

    let page = root._friendsPage;
    if (!page) {
        page = new FriendsPage(root);
        root._friendsPage = page;
    }

    const hash = window.location.hash.replace('#', '');
    if (['all', 'online', 'incoming', 'outgoing'].includes(hash)) {
        page.activate(hash);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
