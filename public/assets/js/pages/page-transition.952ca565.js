import PageTransition from '../core/PageTransition.8719105f.js';

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
