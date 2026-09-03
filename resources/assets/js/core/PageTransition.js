/**
 * PageTransition — SPA-style AJAX navigation.
 *
 * Intercepts internal <a> clicks, fetches a server-rendered fragment (JSON
 * envelope) for the destination and swaps only the content region, updating
 * the URL via history.pushState and mounting the page's JS modules. A thin
 * progress bar is shown at the very top while the fragment is being fetched.
 *
 * The server still renders the full document, so authentication, CSRF and any
 * redirect/error handling stay 100% server-side. If a transition cannot be
 * applied (different layout shell, redirect, error), the client falls back to
 * a normal full-page navigation — keeping the system safe and predictable.
 *
 * Page modules (js/pages/*.js) must export an `init()` function so this router
 * can re-mount them against the freshly inserted DOM after each swap.
 */
export default class PageTransition {
    constructor() {
        // Deferred module scripts run after parsing, so the DOM is ready here.
        this.currentShell = document.querySelector('.page-content')
            ? 'main'
            : 'home';
        this.loadedJs = this.seedLoadedJs();
        this.busy = false;
        this.progress = this.createProgressBar();
        this.progressStart = 0;
        this.progressToken = 0;
    }

    attach() {
        document.addEventListener('click', (e) => this.onClick(e));
        window.addEventListener('popstate', () => this.onPopstate());
    }

    /** Current JS module URLs already present in the document (initial load). */
    seedLoadedJs() {
        const set = new Set();
        for (const s of document.querySelectorAll('script[type="module"]')) {
            const u = s.getAttribute('src');
            if (u) set.add(this.normalize(u));
        }
        return set;
    }

    normalize(url) {
        try {
            return new URL(url, window.location.href).pathname;
        } catch (e) {
            return url;
        }
    }

    createProgressBar() {
        const el = document.createElement('div');
        el.className = 'spa-progress';
        el.style.display = 'none';
        document.body.appendChild(el);
        return el;
    }

    showProgress() {
        const token = ++this.progressToken;
        this.progressStart = performance.now();
        this.progress.style.display = 'block';
        this.progress.classList.add('spa-progress-active');
        this.progress.style.width = '20%';
        return token;
    }

    finishProgress(token) {
        // Keep the bar visible for a readable minimum even when the fetch is
        // near-instant (local pages), so the user actually perceives it.
        const elapsed = performance.now() - this.progressStart;
        const minVisible = 450;
        const wait = Math.max(0, minVisible - elapsed);

        setTimeout(() => {
            if (token !== this.progressToken) return;
            this.progress.style.width = '100%';
            setTimeout(() => {
                if (token !== this.progressToken) return;
                this.progress.classList.remove('spa-progress-active');
                this.progress.style.width = '0';
                this.progress.style.display = 'none';
            }, 250);
        }, wait);
    }

    onClick(e) {
        if (this.busy) return;
        if (e.defaultPrevented) return;

        const link = e.target.closest('a');
        if (!link) return;

        const url = this.resolveLink(link);
        if (!url) return;

        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;
        if (link.hasAttribute('data-no-spa')) return;
        if (/external/.test(link.getAttribute('rel') || '')) return;

        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search) {
            return;
        }

        e.preventDefault();
        this.navigate(url.pathname + url.search + url.hash);
    }

    resolveLink(link) {
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#')) return null;
        if (/^(mailto:|tel:|javascript:)/i.test(href)) return null;
        try {
            return new URL(href, window.location.href);
        } catch (e) {
            return null;
        }
    }

    async navigate(href) {
        this.busy = true;
        const token = this.showProgress();

        try {
            const response = await fetch(href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Hive-Spa': '1',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                redirect: 'manual',
            });

            if (response.type === 'opaqueredirect' || !response.ok) {
                this.hardNavigate(href);
                return;
            }

            const envelope = await response.json();

            if (!envelope || envelope.status !== 'ok' || envelope.shell !== this.currentShell) {
                this.hardNavigate(href);
                return;
            }

            this.apply(envelope);
            window.history.pushState({ hive: true }, envelope.title || '', envelope.url || href);
            this.mountPageModule(envelope);
        } catch (err) {
            this.hardNavigate(href);
        } finally {
            this.finishProgress(token);
            this.busy = false;
        }
    }

    apply(envelope) {
        const container = this.contentContainer();
        if (container) {
            container.innerHTML = envelope.html;
        }

        if (envelope.title && document.title !== envelope.title) {
            document.title = envelope.title;
        }

        this.loadCss(envelope.css);
        this.ensureJsLoaded(envelope.js);

        window.scrollTo({ top: 0, behavior: 'auto' });
    }

    contentContainer() {
        return this.currentShell === 'main'
            ? document.querySelector('.page-content')
            : document.querySelector('.container');
    }

    loadCss(css) {
        (css || []).forEach(href => {
            const present = document.querySelector(`link[href="${href}"]`);
            if (present) return;
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        });
    }

    ensureJsLoaded(js) {
        (js || []).forEach(entry => {
            const path = this.normalize(entry);
            if (this.loadedJs.has(path)) return;
            this.loadedJs.add(path);
            this.appendModuleScript(entry);
        });
    }

    appendModuleScript(entry) {
        const script = document.createElement('script');
        script.type = 'module';
        script.src = entry;
        document.body.appendChild(script);
    }

    mountPageModule(envelope) {
        // Re-run the page's init() on the freshly inserted DOM.
        (envelope.js || []).forEach(entry => {
            if (/pages\/shell\./.test(this.normalize(entry))) return;
            import(entry).then(m => {
                const init = m.init || m.default?.init;
                if (typeof init === 'function') init();
            }).catch(err => console.warn('SPA: mount failed', entry, err));
        });
    }

    onPopstate() {
        const href = window.location.pathname + window.location.search + window.location.hash;
        this.staticNavigate(href);
    }

    staticNavigate(href) {
        this.busy = true;
        const token = this.showProgress();

        fetch(href, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-Hive-Spa': '1' },
            credentials: 'same-origin',
            redirect: 'manual',
        })
            .then(r => (r.type === 'opaqueredirect' || !r.ok) ? null : r.json())
            .then(envelope => {
                if (!envelope || envelope.status !== 'ok' || envelope.shell !== this.currentShell) {
                    window.location.reload();
                    return;
                }
                this.apply(envelope);
                this.mountPageModule(envelope);
            })
            .catch(() => window.location.reload())
            .finally(() => {
                this.finishProgress(token);
                this.busy = false;
            });
    }

    hardNavigate(href) {
        window.location.assign(href);
    }
}
