/**
 * MessagesPage — old-VK style two-pane messenger.
 *
 * Renders the conversation list on the left, opened thread + composer on the
 * right. Sends messages over POST /api/messages/{id}/send (via Ajax.post, which
 * attaches the CSRF token) and receives new messages live from RealtimeBus
 * (`message.event`), refreshing the list and, if the arrived message belongs to
 * the currently open thread, reloading that thread.
 *
 * The client re-reads authoritative data from the API after any change and
 * renders through DOM textContent only (no innerHTML with raw payloads), so it
 * cannot be tricked into injecting markup (XSS-safe).
 */
import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import RealtimeBus from '../core/RealtimeBus.4e82d335.js';

export default class MessagesPage {
    constructor(root) {
        this.root = root;
        if (!root) return;

        this.viewerId = Number(root.getAttribute('data-viewer-id'));
        this.currentOtherId = null;
        this.loading = false;
        this.typingTimeout = null;
        this.typingIndicator = Dom.qs('[data-typing-indicator]', root);

        this.list = Dom.qs('[data-messages-list]', root);
        this.threadHead = Dom.qs('[data-thread-name]', root);
        this.bubbles = Dom.qs('[data-thread-bubbles]', root);
        this.scroll = Dom.qs('[data-thread-scroll]', root);
        this.composer = Dom.qs('[data-composer]', root);
        this.input = Dom.qs('[data-composer-input]', root);

        this.bind();
        this.connectRealtime();
        this.openInitial();
    }

    bind() {
        // Event delegation: the list is rebuilt live by refreshConversations().
        this.list?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-thread]');
            if (btn) this.openThread(btn.getAttribute('data-other-id'));
        });

        this.composer?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.send();
        });

        // Enter to send, Shift+Enter for newline.
        this.input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.send();
            }
        });

        // Emit typing indicator on input (debounced).
        this.input?.addEventListener('input', () => {
            this.emitTyping();
        });
    }

    emitTyping() {
        if (this.currentOtherId === null) return;
        clearTimeout(this.typingTimeout);
        RealtimeBus.emit('user:typing', { to: Number(this.currentOtherId) });
        this.typingTimeout = setTimeout(() => { this.typingTimeout = null; }, 3000);
    }

    connectRealtime() {
        RealtimeBus.start();
        RealtimeBus.on((data) => {
            console.log('[typing] realtime event received:', data?.event, data?.payload);
            if (!data || data.event === 'message.event') {
                this.refreshConversations().then((ok) => {
                    if (ok && data?.payload?.otherId && Number(data.payload.otherId) === Number(this.currentOtherId)) {
                        this.openThread(this.currentOtherId);
                    }
                });
            } else if (data.event === 'message.read') {
                this.refreshConversations();
            } else if (data.event === 'user.typing') {
                this.showTyping(data.payload);
            }
        });
    }

    showTyping(payload) {
        if (!this.typingIndicator) { console.log('[typing] no indicator element'); return; }
        if (!payload || Number(payload.from) !== Number(this.currentOtherId)) {
            console.log('[typing] skipped: payload.from=', payload?.from, 'currentOtherId=', this.currentOtherId);
            return;
        }
        console.log('[typing] SHOWING indicator for', payload.from);

        this.typingIndicator.hidden = false;
        clearTimeout(this._typingHideTimer);
        this._typingHideTimer = setTimeout(() => {
            this.typingIndicator.hidden = true;
        }, 4000);
    }

    openInitial() {
        const openTo = this.root.getAttribute('data-open-to');
        if (openTo) {
            this.openThread(openTo);
            return;
        }
        const first = Dom.qs('[data-thread]', this.list);
        if (first) this.openThread(first.getAttribute('data-other-id'));
    }

    async openThread(otherId) {
        this.currentOtherId = otherId;

        for (const el of Dom.qsa('[data-thread]', this.list)) {
            el.classList.toggle('is-active', el.getAttribute('data-other-id') === String(otherId));
        }

        this.loading = true;
        try {
            const res = await fetch(`/api/messages/${encodeURIComponent(otherId)}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.status !== 'ok') return;

            this.threadHead.textContent = data.other_name || 'Сообщения';
            this.renderThread(data.thread || []);
            this.zeroThreadBadge(data.other_id);
            document.dispatchEvent(new CustomEvent('notifications:refresh'));
        } finally {
            this.loading = false;
        }
    }

    renderThread(items) {
        this.bubbles.textContent = '';
        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'messages-empty';
            const p = document.createElement('p');
            p.className = 'messages-empty-text';
            p.textContent = 'Напишите первым сообщение.';
            empty.appendChild(p);
            this.bubbles.appendChild(empty);
        } else {
            for (const m of items) this.bubbles.appendChild(this.buildBubble(m));
        }
        this.scrollToBottom();
    }

    buildBubble(m) {
        const mine = Number(m.sender_id) === this.viewerId;
        const div = document.createElement('div');
        div.className = 'message-bubble ' + (mine ? 'message-bubble--out' : 'message-bubble--in');
        div.textContent = m.body;

        const meta = document.createElement('span');
        meta.className = 'message-bubble-meta';
        meta.textContent = this.formatTime(m.created_at);
        div.appendChild(meta);
        return div;
    }

    scrollToBottom() {
        if (this.scroll) this.scroll.scrollTop = this.scroll.scrollHeight;
    }

    async send() {
        const body = (this.input.value || '').trim();
        if (!body || this.currentOtherId === null || this.loading) return;

        try {
            const data = await Ajax.post(
                `/api/messages/${encodeURIComponent(this.currentOtherId)}/send`,
                { body }
            );
            if (data.status !== 'ok') return;
            this.input.value = '';
            await Promise.all([
                this.openThread(this.currentOtherId),
                this.refreshConversations(),
            ]);
        } catch (e) {
            /* keep the typed text on failure so the user can retry */
        }
    }

    async refreshConversations() {
        try {
            const res = await fetch('/api/messages/conversations', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return false;
            const data = await res.json();
            if (data.status !== 'ok') return false;

            this.renderList(data.conversations || []);
            return true;
        } catch (e) {
            return false;
        }
    }

    renderList(items) {
        if (!this.list) return;
        this.list.textContent = '';

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'messages-empty';
            const p = document.createElement('p');
            p.className = 'messages-empty-text';
            p.textContent = 'У вас пока нет диалогов.';
            empty.appendChild(p);
            this.list.appendChild(empty);
            return;
        }

        for (const c of items) this.list.appendChild(this.buildThread(c));

        // Keep the active highlight after a live refresh.
        for (const el of Dom.qsa('[data-thread]', this.list)) {
            if (el.getAttribute('data-other-id') === String(this.currentOtherId)) {
                el.classList.add('is-active');
            }
        }
    }

    buildThread(c) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'message-thread';
        btn.setAttribute('data-thread', '');
        btn.setAttribute('data-other-id', String(c.other_id));
        btn.setAttribute('data-conversation-id', c.conversation_id);

        const avatar = document.createElement('span');
        avatar.className = 'message-thread-avatar';
        if (c.other_avatar) {
            const img = document.createElement('img');
            img.src = c.other_avatar;
            img.alt = '';
            img.loading = 'lazy';
            avatar.appendChild(img);
        } else {
            const initials = document.createElement('span');
            initials.className = 'message-thread-initials';
            initials.textContent = (c.other_name || '?').charAt(0).toUpperCase();
            avatar.appendChild(initials);
        }
        btn.appendChild(avatar);

        const body = document.createElement('span');
        body.className = 'message-thread-body';

        const name = document.createElement('span');
        name.className = 'message-thread-name';
        name.textContent = c.other_name;
        body.appendChild(name);

        if (c.last_body) {
            const preview = document.createElement('span');
            preview.className = 'message-thread-preview';
            if (Number(c.last_sender) === this.viewerId) {
                const me = document.createElement('span');
                me.className = 'message-thread-me';
                me.textContent = 'Вы: ';
                preview.appendChild(me);
            }
            preview.appendChild(document.createTextNode(c.last_body));
            body.appendChild(preview);
        }
        btn.appendChild(body);

        if (c.unread) {
            const badge = document.createElement('span');
            badge.className = 'message-thread-badge';
            badge.textContent = String(c.unread);
            badge.setAttribute('data-thread-badge', '');
            btn.appendChild(badge);
        }

        return btn;
    }

    zeroThreadBadge(otherId) {
        const btn = Dom.qs(`[data-thread][data-other-id="${otherId}"]`, this.list);
        const badge = btn && Dom.qs('[data-thread-badge]', btn);
        if (badge) badge.remove();
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

/* ============================================================
   PAGE INIT (re-mounted by SPA router)
   ============================================================ */
export function init() {
    const root = Dom.qs('[data-messages-page]');
    if (!root) return;

    if (root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    new MessagesPage(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

