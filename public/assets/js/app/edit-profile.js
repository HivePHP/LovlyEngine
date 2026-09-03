import Dom from '../core/Dom.js';
import EditProfileForm from '../forms/EditProfileForm.js';

document.addEventListener('DOMContentLoaded', () => {
    new EditProfileForm(
        Dom.qs('#edit-profile-form'),
        {
            successBox: Dom.qs('#success-message'),
            saveBtn: Dom.qs('#save-btn'),
        }
    );
});
