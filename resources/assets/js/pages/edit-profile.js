import Dom from '../core/Dom.js';
import EditProfileForm from '../forms/EditProfileForm.js';

export function init() {
    new EditProfileForm(
        Dom.qs('#edit-profile-form'),
        {
            successBox: Dom.qs('#success-message'),
            saveBtn: Dom.qs('#save-btn'),
        }
    );
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
