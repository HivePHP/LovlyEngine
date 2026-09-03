import StatusWidget from '../forms/StatusWidget.3877ce7f.js';

export function init() {
    new StatusWidget();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
