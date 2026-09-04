import StatusWidget from '../forms/StatusWidget.53e3d052.js';
import AvatarWidget from '../forms/AvatarWidget.be632a47.js';
import FriendButton from '../forms/FriendButton.66066ea0.js';
import Dom from '../core/Dom.3c5c29e1.js';

export function init() {
    new StatusWidget();
    new AvatarWidget();
    new FriendButton(Dom.qs('[data-friend-widget]'));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
