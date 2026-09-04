import StatusWidget from '../forms/StatusWidget.js';
import AvatarWidget from '../forms/AvatarWidget.js';
import FriendButton from '../forms/FriendButton.js';
import Dom from '../core/Dom.js';

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
