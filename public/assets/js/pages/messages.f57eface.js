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
        this.selectMode = false;
        this.selectedIds = new Set();

        this.list = Dom.qs('[data-messages-list]', root);
        this.threadHead = Dom.qs('[data-thread-name]', root);
        this.bubbles = Dom.qs('[data-thread-bubbles]', root);
        this.scroll = Dom.qs('[data-thread-scroll]', root);
        this.composer = Dom.qs('[data-composer]', root);
        this.input = Dom.qs('[data-composer-input]', root);
        this.deleteBar = Dom.qs('[data-delete-bar]', root);
        this.deleteCount = Dom.qs('[data-delete-count]', root);
        this.ctxMenu = Dom.qs('[data-ctx-menu]', root);
        this.ctxTargetId = null;

        this._onRealtime = this._onRealtime.bind(this);
        this._onCtxOutside = this._onCtxOutside.bind(this);

        this.bind();
        this.connectRealtime();
        this.openInitial();
    }

    bind() {
        this.list?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-thread]');
            if (btn) this.openThread(btn.getAttribute('data-other-id'));
        });

        this.composer?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.send();
        });

        this.input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.send();
            }
        });

        this.input?.addEventListener('input', () => {
            this.emitTyping();
        });

        /* Select mode toggle */
        Dom.qs('[data-select-toggle]', this.root)?.addEventListener('click', () => {
            this.toggleSelectMode();
        });

        /* Delete buttons */
        Dom.qs('[data-delete-for-me]', this.root)?.addEventListener('click', () => {
            this.deleteSelected('for_me');
        });
        Dom.qs('[data-delete-for-all]', this.root)?.addEventListener('click', () => {
            this.deleteSelected('for_all');
        });
        Dom.qs('[data-delete-cancel]', this.root)?.addEventListener('click', () => {
            this.exitSelectMode();
        });

        /* Delete conversation */
        Dom.qs('[data-conv-delete]', this.root)?.addEventListener('click', () => {
            this.deleteConversation();
        });

        /* Context menu: right-click on bubble */
        this.bubbles?.addEventListener('contextmenu', (e) => {
            const bubble = e.target.closest('.message-bubble');
            if (!bubble) return;
            e.preventDefault();
            const id = bubble.getAttribute('data-message-id');
            if (!id) return;
            this.ctxTargetId = Number(id);
            this.showCtx(e.clientX, e.clientY);
        });

        /* Context menu actions */
        Dom.qs('[data-ctx-delete-for-me]', this.root)?.addEventListener('click', () => {
            if (this.ctxTargetId) this.deleteSingle(this.ctxTargetId, 'for_me');
            this.hideCtx();
        });
        Dom.qs('[data-ctx-delete-for-all]', this.root)?.addEventListener('click', () => {
            if (this.ctxTargetId) this.deleteSingle(this.ctxTargetId, 'for_all');
            this.hideCtx();
        });

        document.addEventListener('click', this._onCtxOutside);
    }

    /* ---- Context menu ---- */
    showCtx(x, y) {
        if (!this.ctxMenu) return;
        this.ctxMenu.hidden = false;
        this.ctxMenu.style.left = x + 'px';
        this.ctxMenu.style.top = y + 'px';
    }

    hideCtx() {
        if (this.ctxMenu) this.ctxMenu.hidden = true;
        this.ctxTargetId = null;
    }

    _onCtxOutside(e) {
        if (this.ctxMenu && !this.ctxMenu.contains(e.target)) {
            this.hideCtx();
        }
    }

    /* ---- Select mode ---- */
    toggleSelectMode() {
        this.selectMode = !this.selectMode;
        this.selectedIds.clear();
        this.updateSelectUI();
        this.renderCheckboxes();
    }

    exitSelectMode() {
        this.selectMode = false;
        this.selectedIds.clear();
        this.updateSelectUI();
        this.renderCheckboxes();
    }

    updateSelectUI() {
        if (this.deleteBar) this.deleteBar.hidden = !this.selectMode || this.selectedIds.size === 0;
        if (this.deleteCount) {
            this.deleteCount.textContent = this.selectedIds.size + ' выбрано';
        }
        this.root.classList.toggle('messages-selecting', this.selectMode);
    }

    renderCheckboxes() {
        for (const bubble of Dom.qsa('.message-bubble', this.bubbles)) {
            const id = bubble.getAttribute('data-message-id');
            let cb = bubble.querySelector('.message-bubble-cb');
            if (this.selectMode) {
                if (!cb) {
                    cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.className = 'message-bubble-cb';
                    cb.setAttribute('data-bubble-cb', '');
                    bubble.prepend(cb);
                }
                cb.checked = this.selectedIds.has(Number(id));
                cb.addEventListener('change', () => {
                    const mid = Number(bubble.getAttribute('data-message-id'));
                    if (cb.checked) {
                        this.selectedIds.add(mid);
                    } else {
                        this.selectedIds.delete(mid);
                    }
                    this.updateSelectUI();
                });
            } else if (cb) {
                cb.remove();
            }
        }
    }

    /* ---- Delete single message ---- */
    async deleteSingle(messageId, mode) {
        try {
            await fetch(`/api/messages/${encodeURIComponent(messageId)}/delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ mode }),
            });
            if (this.currentOtherId) {
                await this.openThread(this.currentOtherId);
            }
            await this.refreshConversations();
        } catch (e) { /* silent */ }
    }

    /* ---- Delete selected messages ---- */
    async deleteSelected(mode) {
        if (this.selectedIds.size === 0) return;
        const ids = [...this.selectedIds];
        try {
            await fetch('/api/messages/delete-batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ ids, mode }),
            });
            this.exitSelectMode();
            if (this.currentOtherId) {
                await this.openThread(this.currentOtherId);
            }
            await this.refreshConversations();
        } catch (e) { /* silent */ }
    }

    /* ---- Delete conversation ---- */
    async deleteConversation() {
        if (this.currentOtherId === null) return;
        if (!confirm('Удалить диалог?')) return;
        try {
            await fetch(`/api/messages/${encodeURIComponent(this.currentOtherId)}/delete-conversation`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ mode: 'for_me' }),
            });
            this.currentOtherId = null;
            this.bubbles.textContent = '';
            this.threadHead.textContent = '';
            await this.refreshConversations();
        } catch (e) { /* silent */ }
    }

    emitTyping() {
        if (this.currentOtherId === null) return;
        clearTimeout(this.typingTimeout);
        RealtimeBus.emit('user:typing', { to: Number(this.currentOtherId) });
        this.typingTimeout = setTimeout(() => { this.typingTimeout = null; }, 3000);
    }

    connectRealtime() {
        RealtimeBus.start();
        RealtimeBus.on(this._onRealtime);
    }

    _onRealtime(data) {
        if (!this.root.isConnected) return;

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
    }

    showTyping(payload) {
        if (!this.typingIndicator) return;
        if (!payload || Number(payload.from) !== Number(this.currentOtherId)) return;

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
        this.exitSelectMode();

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
        div.setAttribute('data-message-id', String(m.id));
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
