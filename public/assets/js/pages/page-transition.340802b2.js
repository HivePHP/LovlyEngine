import PageTransition from '../core/PageTransition.4d307d32.js';

function init() {
    if (document.documentElement.dataset.pageTransition === '1') return;
    document.documentElement.dataset.pageTransition = '1';

    new PageTransition().attach();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
