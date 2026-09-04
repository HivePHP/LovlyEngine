import StatusWidget from '../forms/StatusWidget.53e3d052.js';
import AvatarWidget from '../forms/AvatarWidget.6d90bea1.js';

export function init() {
    new StatusWidget();
    new AvatarWidget();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
