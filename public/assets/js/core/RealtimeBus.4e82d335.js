/**
 * RealtimeBus — a single shared Socket.IO connection for the whole shell.
 *
 * Owns the one socket (authenticated with the short-lived HMAC token that PHP
 * embedded into `window.__REALTIME__`). Every incoming `realtime:event` is
 * relayed to a DOM CustomEvent, so the bell, sidebar badges and page modules
 * (e.g. the messenger) can all react without each creating its own socket.
 *
 * The connection is lazily created once; the header/shell is not swapped by the
 * SPA router, so a single socket lives for the full session.
 */
class RealtimeBus {
    constructor() {
        this.socket = null;
        this.started = false;
    }

    /** Create the socket (once) and start relaying events. Returns the socket or null. */
    start() {
        if (this.started) return this.socket;
        this.started = true;

        const rt = window.__REALTIME__;
        if (!rt || typeof window.io === 'undefined') {
            return null;
        }

        this.socket = window.io(rt.url, {
            path: rt.path,
            transports: ['websocket', 'polling'],
            auth: { token: rt.token },
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 1000,
            reconnectionDelayMax: 5000,
        });

        this.socket.on('realtime:event', (data) => {
            document.dispatchEvent(new CustomEvent('realtime:event', { detail: data }));
        });

        return this.socket;
    }

    /** Register a listener for server events. Returns an unsubscribe fn. */
    on(handler) {
        document.addEventListener('realtime:event', (e) => handler(e.detail));
    }

    /** Emit an event to the server (e.g. 'user:typing'). */
    emit(event, data) {
        if (this.socket) {
            this.socket.emit(event, data);
        }
    }
}

export default new RealtimeBus();
