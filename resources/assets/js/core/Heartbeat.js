import Ajax from './Ajax.js';

const INTERVAL = 60_000;
const VISIBILITY_INTERVAL = 15_000;

export default class Heartbeat {
    constructor() {
        this._timer = null;
        this._visibleTimer = null;
        this._active = false;
    }

    start() {
        if (this._active) return;
        this._active = true;
        this._send();
        this._timer = setInterval(() => this._send(), INTERVAL);
        document.addEventListener('visibilitychange', this._onVisibility);
        window.addEventListener('beforeunload', this._onLeave);
    }

    stop() {
        this._active = false;
        clearInterval(this._timer);
        clearTimeout(this._visibleTimer);
        this._timer = null;
        this._visibleTimer = null;
        document.removeEventListener('visibilitychange', this._onVisibility);
        window.removeEventListener('beforeunload', this._onLeave);
    }

    _send() {
        if (document.hidden) return;
        Ajax.post('/api/online/heartbeat', {}).catch(() => {});
    }

    _onVisibility = () => {
        clearTimeout(this._visibleTimer);
        if (!document.hidden) {
            this._visibleTimer = setTimeout(() => this._send(), VISIBILITY_INTERVAL);
        }
    };

    _onLeave = () => {
        // sendBeacon is reliable for page unload; GET avoids CSRF.
        navigator.sendBeacon('/api/online/leave');
    };
}
