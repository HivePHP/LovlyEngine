import Ajax from './Ajax.js';

const cache = new Map();
let pending = new Set();
let flushTimer = null;

export function setOnline(userId, online) {
    cache.set(userId, online);
    document.dispatchEvent(new CustomEvent('online:changed', {
        detail: { userId, online }
    }));
}

export function getOnline(userId) {
    return cache.get(userId) ?? null;
}

export function requestBatch(userIds) {
    userIds.forEach(id => pending.add(id));
    clearTimeout(flushTimer);
    flushTimer = setTimeout(flush, 50);
}

function flush() {
    const ids = [...pending];
    pending = [];
    if (!ids.length) return;

    Ajax.post('/api/online/batch', { user_ids: ids })
        .then(res => {
            if (res.status === 'ok' && res.online) {
                for (const [id, online] of Object.entries(res.online)) {
                    const uid = parseInt(id, 10);
                    cache.set(uid, online);
                    document.dispatchEvent(new CustomEvent('online:changed', {
                        detail: { userId: uid, online }
                    }));
                }
            }
        })
        .catch(() => {});
}
