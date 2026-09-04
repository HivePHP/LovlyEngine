import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';

const ACTION_URL = {
    accept:  (id) => `/api/friends/${id}/accept`,
    decline: (id) => `/api/friends/${id}/decline`,
    remove:  (id) => `/api/friends/${id}/remove`,
};

const SECTION_BADGE = {
    friends:   'friends',
    incoming:  'incoming',
    outgoing:  'outgoing',
};

function parseCount(text) {
    const n = parseInt(String(text).replace(/\D/g, ''), 10);
    return Number.isFinite(n) ? n : 0;
}

/* ============================================================
   FRIENDS PAGE — tabs + request/accept/decline/remove actions
   ============================================================ */
class FriendsPage {
    constructor(root) {
        this.root = root;
        if (this.root.dataset.bound === '1') return;
        this.root.dataset.bound = '1';

        this.tabs = Dom.qsa('[data-tab]', this.root);
        this.sections = Dom.qsa('[data-section]', this.root);

        this.tabs.forEach((tab) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                this.activate(tab.dataset.tab);
            });
        });

        this.root.addEventListener('click', (e) => this.onActionClick(e));
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

    onActionClick(e) {
        const btn = e.target.closest('[data-friend-action]');
        if (!btn) return;
        e.preventDefault();

        const action = btn.dataset.friendAction;
        const card = btn.closest('[data-friend-card]');
        const id = card?.dataset.friendId;

        if (!ACTION_URL[action] || !id) return;

        // Disable all buttons on this card while the request is in flight.
        Dom.qsa('[data-friend-action]', card).forEach((b) => (b.disabled = true));

        Ajax.post(ACTION_URL[action](id), {})
            .then((payload) => {
                if (payload.status !== 'ok') {
                    throw new Error(payload.message || 'Не удалось выполнить операцию.');
                }
                this.afterAction(action, card);
            })
            .catch((err) => {
                Dom.qsa('[data-friend-action]', card).forEach((b) => (b.disabled = false));
                this.flash(err.message || 'Ошибка сети. Попробуйте ещё раз.');
            });
    }

    afterAction(action, card) {
        const section = card?.parentElement?.closest('[data-section]');
        const sectionName = section?.dataset.section;

        card?.remove();

        // Update tab badges.
        this.updateBadge('friends', action === 'accept' ? 1 : -1, action === 'accept');
        if (sectionName === 'incoming' && (action === 'accept' || action === 'decline')) {
            this.updateBadge('incoming', -1, true);
        }
        if (sectionName === 'outgoing') {
            this.updateBadge('outgoing', -1, true);
        }
        if (sectionName === 'friends' && action === 'remove') {
            this.updateBadge('friends', -1, true);
        }

        // If a section becomes empty, show its empty state.
        const grid = section && Dom.qs('.friends-grid', section);
        if (grid && grid.children.length === 0) {
            section.innerHTML = '<div class="friends-empty"><p class="friends-empty-text">Здесь пока пусто.</p></div>';
        }
    }

    updateBadge(sectionName, delta, isAbsolute) {
        const tab = Dom.qs(`[data-tab="${sectionName}"]`, this.root);
        if (!tab) return;
        let badge = Dom.qs('.friends-badge', tab);

        let count = badge ? parseCount(badge.textContent) : 0;
        if (isAbsolute) {
            count = Math.max(0, count + delta);
        } else {
            count = delta === 1 ? count + 1 : Math.max(0, count - 1);
        }

        if (!badge) {
            if (count <= 0) return;
            badge = document.createElement('span');
            badge.className = 'friends-badge';
            tab.appendChild(badge);
        }
        badge.textContent = count;
        if (count <= 0) badge.remove();
    }

    flash(message) {
        let bar = Dom.qs('.friends-flash', this.root);
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'friends-flash';
            this.root.prepend(bar);
        }
        bar.textContent = message;
        bar.classList.add('is-visible');
        clearTimeout(this._flashTimer);
        this._flashTimer = setTimeout(() => bar.classList.remove('is-visible'), 3000);
    }
}

/* ============================================================
   PAGE INIT (re-mounted by SPA router)
   ============================================================ */
export function init() {
    const root = Dom.qs('[data-friends-page]');
    if (!root) return;

    let page = root._friendsPage;
    if (!page) {
        page = new FriendsPage(root);
        root._friendsPage = page;
    }

    // Restore active tab from location hash.
    const hash = window.location.hash.replace('#', '');
    if (['friends', 'incoming', 'outgoing'].includes(hash)) {
        page.activate(hash);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
