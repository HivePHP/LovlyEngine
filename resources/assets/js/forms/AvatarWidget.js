import Dom from '../core/Dom.js';
import Ajax from '../core/Ajax.js';
import Modal from '../ui/Modal.js';
import AvatarCrop from '../ui/AvatarCrop.js';

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_BYTES = 8 * 1024 * 1024;

export default class AvatarWidget extends Modal {
    constructor(
        cls = '[data-avatar-widget]',
        modal = '[data-avatar-modal]',
        input = '[data-avatar-input]'
    ) {
        const widget = Dom.qs(cls);
        const modalRoot = Dom.qs(modal);
        super(modalRoot);

        if (!widget || !modalRoot) {
            return;
        }

        this.widget = widget;
        this.input = Dom.qs(input, widget);

        if (widget.dataset.avatarBound === '1' || !this.input) {
            return;
        }
        widget.dataset.avatarBound = '1';

        this.menu = Dom.qs('[data-avatar-menu]', widget);
        this.toggle = Dom.qs('[data-avatar-toggle]', widget);
        this.avatarBox = Dom.qs('.profile-avatar', widget);
        this.img = Dom.qs('[data-avatar-img]', widget);
        this.fallback = Dom.qs('[data-avatar-fallback]', widget);

        this.menuUpload = Dom.qs('[data-avatar-upload]', widget);
        this.menuUpdate = Dom.qs('[data-avatar-update]', widget);
        this.menuRecrop = Dom.qs('[data-avatar-recrop]', widget);
        this.menuDelete = Dom.qs('[data-avatar-delete]', widget);

        this.cropImage = Dom.qs('[data-avatar-crop-image]', modalRoot);
        this.wrap = Dom.qs('[data-avatar-wrap]', modalRoot);
        this.empty = Dom.qs('[data-avatar-empty]', modalRoot);
        this.confirm = Dom.qs('[data-avatar-confirm]', modalRoot);

        this.uploading = false;
        this.objectUrl = null;
        this.reCropMode = false;

        // Remember the original header initials so the header can be restored
        // when the avatar is removed.
        this.headerInitials = Dom.qs('[data-header-avatar-initials]')?.textContent || '';

        // Закрытие: отменяем кроп и освобождаем URL объекта.
        this.onClose = () => {
            this.clearError();
            this.input.value = '';
            this.reCropMode = false;
            if (this.crop) {
                this.crop.endDrag();
                this.crop = null;
            }
            if (this.objectUrl) {
                URL.revokeObjectURL(this.objectUrl);
                this.objectUrl = null;
            }
        };

        this.syncMenu();
        this.bindMenu();

        if (this.toggle) {
            this.toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleMenu();
            });
        }
        this.input.addEventListener('change', () => this.onFileSelected());

        if (this.confirm) {
            this.confirm.addEventListener('click', () => this.save());
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Dropdown menu                                                      */
    /* ------------------------------------------------------------------ */

    syncMenu() {
        const hasAvatar = !!this.widget.dataset.currentAvatar;
        if (this.menuUpload) this.menuUpload.hidden = hasAvatar;
        if (this.menuUpdate) this.menuUpdate.hidden = !hasAvatar;
        if (this.menuRecrop) this.menuRecrop.hidden = !hasAvatar;
        if (this.menuDelete) this.menuDelete.hidden = !hasAvatar;
    }

    toggleMenu() {
        if (!this.menu) return;
        const open = this.menu.hidden;
        this.menu.hidden = !open;
        if (open) {
            document.addEventListener('click', this._boundOutside);
        } else {
            document.removeEventListener('click', this._boundOutside);
        }
    }

    closeMenu() {
        if (this.menu) {
            this.menu.hidden = true;
            document.removeEventListener('click', this._boundOutside);
        }
    }

    _boundOutside = (e) => {
        if (this.widget && !this.widget.contains(e.target)) {
            this.closeMenu();
        }
    };

    bindMenu() {
        if (this.menuUpload) {
            this.menuUpload.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeMenu();
                this.input.click();
            });
        }
        if (this.menuUpdate) {
            this.menuUpdate.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeMenu();
                this.input.click();
            });
        }
        if (this.menuRecrop) {
            this.menuRecrop.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeMenu();
                this.onRecrop();
            });
        }
        if (this.menuDelete) {
            this.menuDelete.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeMenu();
                this.onDelete();
            });
        }
    }

    /* ------------------------------------------------------------------ */
    /*  File selection (upload / update)                                   */
    /* ------------------------------------------------------------------ */

    onFileSelected() {
        const file = this.input.files && this.input.files[0];
        if (!file) return;

        if (!ALLOWED_TYPES.includes(file.type)) {
            this.input.value = '';
            this.openWithError('Допустимы только JPEG, PNG, WebP или GIF.');
            return;
        }

        if (file.size > MAX_BYTES) {
            this.input.value = '';
            this.openWithError('Файл слишком большой (макс. 8 МБ).');
            return;
        }

        this.openModal(file);
    }

    openWithError(message) {
        this.open();
        this.wrap.hidden = true;
        this.empty.hidden = false;
        this.confirm.hidden = true;
        this.setError(message);
    }

    openModal(file) {
        this.reCropMode = false;
        this.openCropSource(() => URL.createObjectURL(file));
    }

    /* ------------------------------------------------------------------ */
    /*  Re-crop from the stored original                                    */
    /* ------------------------------------------------------------------ */

    onRecrop() {
        const original = this.widget.dataset.avatarOriginal || '';
        if (!original) {
            // No stored original available — fall back to picking a new image.
            this.input.click();
            return;
        }

        this.reCropMode = true;
        this.openCropSource(() => original);
    }

    openCropSource(buildSrc) {
        this.clearError();
        this.open();
        this.wrap.hidden = true;
        this.empty.hidden = false;
        this.confirm.hidden = true;

        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        const src = buildSrc();
        this.objectUrl = src.startsWith('blob:') ? src : null;

        this.cropImage.onload = () => {
            this.empty.hidden = true;
            this.wrap.hidden = false;
            this.crop = new AvatarCrop(this.cropImage);
            requestAnimationFrame(() => requestAnimationFrame(() => this.crop.layout()));
            this.confirm.hidden = false;
        };
        this.cropImage.onerror = () => this.openWithError('Не удалось прочитать изображение.');
        this.cropImage.src = src;
    }

    /* ------------------------------------------------------------------ */
    /*  Save                                                               */
    /* ------------------------------------------------------------------ */

    async save() {
        if (this.uploading || !this.crop) return;
        this.uploading = true;
        this.clearError();

        try {
            const blob = await this.crop.crop(512);

            const formData = new FormData();
            formData.append('avatar', blob, 'avatar.jpg');
            if (this.reCropMode) {
                // Re-crop of the same photo: server repoints the album photo,
                // no duplicate original is stored.
                formData.append('reCrop', '1');
            } else {
                const original = this.input.files && this.input.files[0];
                if (original) {
                    // Send the un-cropped original too, so the server can keep it
                    // in the "photos on my page" album.
                    formData.append('original', original, original.name || 'original.jpg');
                }
            }
            formData.append('csrf_token', Ajax.csrfToken());

            const response = await fetch('/api/profile/avatar', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
                credentials: 'same-origin',
            });

            let payload;
            try {
                payload = await response.json();
            } catch (e) {
                throw new Error('Некорректный ответ сервера');
            }

            if (response.status !== 200 || payload.status !== 'ok') {
                throw new Error(payload.message || 'Не удалось сохранить аватар.');
            }

            this.setAvatarImage(payload.url ?? '');
            this.close();
        } catch (err) {
            this.setError(err.message || 'Ошибка сети. Попробуйте ещё раз.');
        } finally {
            this.uploading = false;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Delete avatar                                                      */
    /* ------------------------------------------------------------------ */

    async onDelete() {
        const current = this.widget.dataset.currentAvatar;
        if (!current) return;

        if (!window.confirm('Удалить аватарку?')) return;

        try {
            const payload = await Ajax.post('/api/profile/avatar/delete', {}, {
                headers: { 'Accept': 'application/json' },
            });

            if (payload.status !== 'ok') {
                throw new Error(payload.message || 'Не удалось удалить аватарку.');
            }

            this.setAvatarImage(payload.avatar ? payload.avatar.url : '');
        } catch (err) {
            window.alert(err.message || 'Ошибка сети. Попробуйте ещё раз.');
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Rendering helpers                                                  */
    /* ------------------------------------------------------------------ */

    setAvatarImage(url) {
        this.widget.dataset.currentAvatar = url || '';

        if (this.fallback) {
            this.fallback.remove();
            this.fallback = null;
        }

        if (url) {
            if (!this.img) {
                this.img = document.createElement('img');
                this.img.className = 'profile-avatar-img';
                this.img.dataset.avatarImg = '';
                this.img.alt = 'Аватар';
                this.avatarBox.insertBefore(this.img, this.avatarBox.firstChild);
            }
            this.img.src = url;
        } else {
            if (this.img) {
                this.img.remove();
                this.img = null;
            }
        }

        this.syncMenu();
        this.updateHeaderAvatars(url);
    }

    // Обновим аватар в шапке (кружок справа) и в выпадающем меню.
    updateHeaderAvatars(url) {
        const header = Dom.qs('[data-header-avatar]', document);
        if (header) {
            if (url) {
                let headImg = Dom.qs('[data-header-avatar-img]', header);
                if (!headImg) {
                    const initials = Dom.qs('[data-header-avatar-initials]', header);
                    if (initials) initials.remove();
                    headImg = document.createElement('img');
                    headImg.className = 'header-avatar-img';
                    headImg.dataset.headerAvatarImg = '';
                    headImg.alt = 'Аватар';
                    header.appendChild(headImg);
                }
                headImg.src = url;
            } else {
                const headImg = Dom.qs('[data-header-avatar-img]', header);
                if (headImg) {
                    const container = headImg.parentElement;
                    headImg.remove();
                    if (this.headerInitials && !Dom.qs('[data-header-avatar-initials]', container)) {
                        const span = document.createElement('span');
                        span.className = 'header-avatar-initials';
                        span.dataset.headerAvatarInitials = '';
                        span.textContent = this.headerInitials;
                        container.appendChild(span);
                    }
                }
            }
        }

        const dropdown = Dom.qs('[data-header-dropdown-avatar]', document);
        if (dropdown) {
            if (url) {
                let dropImg = Dom.qs('[data-header-dropdown-avatar-img]', dropdown);
                if (!dropImg) {
                    const initials = Dom.qs('[data-header-dropdown-avatar-initials]', dropdown);
                    if (initials) initials.remove();
                    dropImg = document.createElement('img');
                    dropImg.className = 'header-dropdown-avatar-img';
                    dropImg.dataset.headerDropdownAvatarImg = '';
                    dropImg.alt = 'Аватар';
                    dropdown.appendChild(dropImg);
                }
                dropImg.src = url;
            } else {
                const dropImg = Dom.qs('[data-header-dropdown-avatar-img]', dropdown);
                if (dropImg) {
                    const container = dropImg.parentElement;
                    dropImg.remove();
                    if (this.headerInitials && !Dom.qs('[data-header-dropdown-avatar-initials]', container)) {
                        const span = document.createElement('span');
                        span.className = 'header-dropdown-avatar-initials';
                        span.dataset.headerDropdownAvatarInitials = '';
                        span.textContent = this.headerInitials;
                        container.appendChild(span);
                    }
                }
            }
        }
    }
}
