import Dom from './Dom.js';
import Dropdown from './Dropdown.js';
import Ajax from './Ajax.js';
import RealtimeBus from './RealtimeBus.js';

/**
 * NotificationBell — header bell + live sidebar badges.
 *
 * - Opens a Socket.IO connection authenticated with the short-lived HMAC token
 *   that the PHP backend embedded into `window.__REALTIME__` (never our own
 *   trust decision on the client).
 * - On a `realtime:event` from the server, refetches the authoritative list
 *   from GET /api/notifications and re-renders the bell + badges. The client
 *   never trusts the raw websocket payload for markup — it always renders from
 *   the server response.
 * - Everything that touches the DOM is built with textContent / DOM APIs, so
 *   actor names and payloads cannot inject markup (XSS-safe).
 *
 * The socket lives for the lifetime of the shell (the header is not swapped by
 * the SPA router), so this module is mounted once from shell.js.
 */
export default class NotificationBell {
    constructor(root) {
        this.root = root;
        if (!root) return;

        this.rt = window.__REALTIME__ || null;
        this.cfg = null; // client config fallback (from /api/notifications)
        this._prevUnread = 0;
        this._notifReady = false;
        this._sound = new Audio('/assets/sound/notif.wav');
        this._sound.volume = 0.6;

        this.toggle = Dom.qs('[data-notif-toggle]', root);
        this.menu = Dom.qs('[data-notif-dropdown]', root);
        this.badge = Dom.qs('[data-notif-badge]', root);
        this.list = Dom.qs('[data-notif-list]', root);
        this.markAll = Dom.qs('[data-notif-markall]', root);

        this.dropdown = new Dropdown(this.toggle, this.menu, {
            onOpen: () => this.onOpen(),
        });

        this.bind();
        this.connect();
    }

    bind() {
        // Mark a single notification read when its row is clicked.
        this.list?.addEventListener('click', (e) => {
            const item = e.target.closest('[data-notif-item]');
            if (!item) return;
            const id = item.getAttribute('data-notif-id');
            if (id) {
                this.markRead(id).catch(() => {});
            }
            // Let the anchor navigation (or SPA push) proceed.
        });

        this.markAll?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.markAllRead();
        });

        // Let other page modules (e.g. the friends page after accept/decline)
        // ask us to re-read the authoritative counts even without a live socket.
        document.addEventListener('notifications:refresh', () => this.refresh());
    }

    connect() {
        if (!this.rt) return;
        if (typeof window.io === 'undefined') {
            console.warn('[notif] socket.io client not loaded');
            return;
        }

        // One shared socket for the whole shell (see RealtimeBus).
        // React to any server event by re-reading authoritative counts.
        RealtimeBus.start();
        RealtimeBus.on(() => this.refresh());
    }

    onOpen() {
        // Opening the bell is a good moment to fetch fresh data.
        this.refresh();
    }

    /** GET /api/notifications → re-render list + badges. */
    async refresh() {
        try {
            const res = await fetch('/api/notifications', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.status !== 'ok') return;

            this.renderList(data.items || []);
            this.setBadge(data.unread || 0, data.unread_by_section || {});
        } catch (e) {
            /* ignore transient failures */
        }
    }

    renderList(items) {
        if (!this.list) return;
        this.list.textContent = '';

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'header-notif-empty';
            empty.textContent = 'Уведомлений пока нет';
            this.list.appendChild(empty);
            return;
        }

        for (const item of items) {
            this.list.appendChild(this.buildItem(item));
        }
    }

    buildItem(n) {
        const a = document.createElement('a');
        a.href = n.link || '#';
        a.className = 'header-notif-item' + (n.is_read ? '' : ' is-unread');
        a.setAttribute('data-notif-item', '');
        a.setAttribute('data-notif-id', n.id);

        const avatar = document.createElement('div');
        avatar.className = 'header-notif-avatar';
        if (n.actor_avatar) {
            const img = document.createElement('img');
            img.className = 'header-notif-avatar-img';
            img.src = n.actor_avatar;
            img.alt = '';
            avatar.appendChild(img);
        } else {
            const initials = document.createElement('span');
            initials.className = 'header-notif-initials';
            initials.textContent = this.initials(n.actor_name, n.actor_surname);
            avatar.appendChild(initials);
        }

        const body = document.createElement('div');
        body.className = 'header-notif-body';

        const text = document.createElement('div');
        text.className = 'header-notif-text';
        const name = document.createElement('span');
        name.className = 'header-notif-actor';
        name.textContent = this.displayName(n.actor_name, n.actor_surname);
        text.appendChild(name);
        if (n.type === 'friend.request') {
            text.appendChild(document.createTextNode(' отправил(а) вам заявку в друзья'));
        } else if (n.type === 'friend.accepted') {
            text.appendChild(document.createTextNode(' принял(а) вашу заявку в друзья'));
        } else {
            text.appendChild(document.createTextNode(' ' + n.type));
        }

        const time = document.createElement('div');
        time.className = 'header-notif-time';
        time.textContent = this.formatTime(n.created_at);

        body.appendChild(text);
        body.appendChild(time);
        a.appendChild(avatar);
        a.appendChild(body);
        return a;
    }

    _dialogOpen() {
        const active = document.querySelector('[data-messages-list] [data-thread].is-active');
        return !!active;
    }

    /** Update bell badge + sidebar badges from server-counted data. */
    setBadge(unread, bySection) {
        if (this._notifReady && unread > this._prevUnread && !this._dialogOpen()) {
            try { this._sound.play(); } catch (_) { /* autoplay blocked */ }
        }
        this._prevUnread = unread;
        this._notifReady = true;

        if (this.badge) {
            this.badge.hidden = unread <= 0;
            this.badge.textContent = String(unread);
        }
        if (this.markAll) {
            this.markAll.hidden = unread <= 0;
        }

        // Sidebar badges (e.g. Друзья (N)).
        for (const el of Dom.qsa('[data-nav-badge]')) {
            const section = el.getAttribute('data-nav-badge');
            const count = bySection[section] || 0;
            el.hidden = count <= 0;
            el.textContent = String(count);
        }
    }

    async markRead(id) {
        try {
            await Ajax.post(`/api/notifications/${encodeURIComponent(id)}/read`, {});
            this.refresh();
        } catch (e) {
            /* best effort */
        }
    }

    async markAllRead() {
        try {
            await Ajax.post('/api/notifications/read-all', {});
            this.refresh();
        } catch (e) {
            /* best effort */
        }
    }

    displayName(name, surname) {
        return [name, surname].filter(Boolean).join(' ');
    }

    initials(name, surname) {
        const a = (name || '')[0] || '';
        const b = (surname || '')[0] || '';
        return (a + b).toUpperCase();
    }

    formatTime(value) {
        if (!value) return '';
        const ms = Date.parse(String(value).replace(' ', 'T'));
        if (Number.isNaN(ms)) return String(value).slice(0, 16);

        const diff = (Date.now() - ms) / 1000;
        const min = Math.floor(diff / 60);
        if (min < 1) return 'только что';
        if (min < 60) return `${min} мин назад`;
        const hr = Math.floor(min / 60);
        if (hr < 24) return `${hr} ч назад`;
        const d = Math.floor(hr / 24);
        if (d < 7) return `${d} дн назад`;

        return new Date(ms).toLocaleString('ru-RU', {
            day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
        });
    }
}
