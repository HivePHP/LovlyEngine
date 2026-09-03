import Dom from '../core/Dom.3c5c29e1.js';
import EditProfileForm from '../forms/EditProfileForm.c9e6b30f.js';

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
