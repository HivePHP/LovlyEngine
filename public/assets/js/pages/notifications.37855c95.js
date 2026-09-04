import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';

export default class NotificationsPage {
    constructor(root) {
        this.root = root;
        if (!root) return;

        this.list = Dom.qs('[data-notif-list]', root);
        this.markAllBtn = Dom.qs('[data-notif-markall]', root);
        this.deleteAllBtn = Dom.qs('[data-notif-deleteall]', root);

        this.bind();
        this.renderInitial();
    }

    bind() {
        this.list?.addEventListener('click', (e) => {
            const readBtn = e.target.closest('[data-notif-action="read"]');
            if (readBtn) {
                this.markRead(readBtn);
                return;
            }
            const deleteBtn = e.target.closest('[data-notif-action="delete"]');
            if (deleteBtn) {
                this.deleteItem(deleteBtn);
                return;
            }
        });

        this.markAllBtn?.addEventListener('click', () => this.markAllRead());
        this.deleteAllBtn?.addEventListener('click', () => this.deleteAll());
    }

    renderInitial() {
        const items = this.list?.querySelectorAll('[data-notif-item]');
        if (!items || items.length) return;

        this.list.textContent = '';
        this.list.appendChild(this.buildEmpty());
    }

    buildEmpty() {
        const div = document.createElement('div');
        div.className = 'notif-empty';
        div.innerHTML = '<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#b0b8c1" stroke-width="1.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>';
        const p = document.createElement('p');
        p.textContent = 'Уведомлений пока нет';
        div.appendChild(p);
        return div;
    }

    async markRead(btn) {
        const id = btn.closest('[data-notif-id]')?.getAttribute('data-notif-id');
        if (!id) return;
        try {
            const data = await Ajax.post(`/api/notifications/${encodeURIComponent(id)}/read`, {});
            const item = btn.closest('[data-notif-item]');
            if (item) item.classList.remove('is-unread');
            btn.remove();
            document.dispatchEvent(new CustomEvent('notifications:refresh'));
        } catch (_) {}
    }

    async deleteItem(btn) {
        const id = btn.closest('[data-notif-id]')?.getAttribute('data-notif-id');
        if (!id) return;
        try {
            const data = await Ajax.post(`/api/notifications/${encodeURIComponent(id)}/delete`, {});
            const item = btn.closest('[data-notif-item]');
            if (item) {
                item.style.transition = 'opacity 0.2s ease';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    if (!this.list?.querySelector('[data-notif-item]')) {
                        this.list.appendChild(this.buildEmpty());
                    }
                }, 200);
            }
            document.dispatchEvent(new CustomEvent('notifications:refresh'));
        } catch (_) {}
    }

    async markAllRead() {
        try {
            await Ajax.post('/api/notifications/read-all', {});
            for (const item of this.list?.querySelectorAll('[data-notif-item]') || []) {
                item.classList.remove('is-unread');
                const btn = item.querySelector('[data-notif-action="read"]');
                if (btn) btn.remove();
            }
            document.dispatchEvent(new CustomEvent('notifications:refresh'));
        } catch (_) {}
    }

    async deleteAll() {
        try {
            await Ajax.post('/api/notifications/delete-all', {});
            this.list.textContent = '';
            this.list.appendChild(this.buildEmpty());
            document.dispatchEvent(new CustomEvent('notifications:refresh'));
        } catch (_) {}
    }
}

/* ============================================================
   PAGE INIT (re-mounted by SPA router)
   ============================================================ */
export function init() {
    const root = Dom.qs('[data-notif-page]');
    if (!root) return;
    if (root.dataset.bound === '1') return;
    root.dataset.bound = '1';
    new NotificationsPage(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
