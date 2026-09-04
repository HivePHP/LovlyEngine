import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import Modal from '../ui/Modal.c0ac6b17.js';
import AvatarCrop from '../ui/AvatarCrop.5c86e8c5.js';

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

        if (!widget || !modalRoot || !input) {
            return;
        }

        this.widget = widget;
        this.input = Dom.qs(input, widget);

        if (widget.dataset.avatarBound === '1' || !this.input) {
            return;
        }
        widget.dataset.avatarBound = '1';

        this.toggle = Dom.qs('[data-avatar-toggle]', widget);
        this.img = Dom.qs('[data-avatar-img]', widget);
        this.fallback = Dom.qs('[data-avatar-fallback]', widget);

        this.cropImage = Dom.qs('[data-avatar-crop-image]', modalRoot);
        this.wrap = Dom.qs('[data-avatar-wrap]', modalRoot);
        this.empty = Dom.qs('[data-avatar-empty]', modalRoot);
        this.confirm = Dom.qs('[data-avatar-confirm]', modalRoot);

        this.uploading = false;
        this.objectUrl = null;

        // Закрытие: отменяем кроп и освобождаем URL объекта.
        this.onClose = () => {
            this.clearError();
            this.input.value = '';
            if (this.crop) {
                this.crop.endDrag();
                this.crop = null;
            }
            if (this.objectUrl) {
                URL.revokeObjectURL(this.objectUrl);
                this.objectUrl = null;
            }
        };

        this.toggle.addEventListener('click', () => this.input.click());
        this.input.addEventListener('change', () => this.onFileSelected());

        if (this.confirm) {
            this.confirm.addEventListener('click', () => this.save());
        }
    }

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
        this.clearError();
        this.open();
        this.wrap.hidden = true;
        this.empty.hidden = false;
        this.confirm.hidden = true;

        if (this.objectUrl) URL.revokeObjectURL(this.objectUrl);
        this.objectUrl = URL.createObjectURL(file);

        const img = this.cropImage;
        img.onload = () => {
            this.empty.hidden = true;
            this.wrap.hidden = false;
            this.crop = new AvatarCrop(img);
            // Wait a frame so the frame has laid out before measuring.
            requestAnimationFrame(() => requestAnimationFrame(() => this.crop.layout()));
            this.confirm.hidden = false;
        };
        img.onerror = () => this.openWithError('Не удалось прочитать изображение.');
        img.src = this.objectUrl;
    }

    async save() {
        if (this.uploading || !this.crop) return;
        this.uploading = true;
        this.clearError();

        try {
            const blob = await this.crop.crop(512);
            const original = this.input.files && this.input.files[0];

            const formData = new FormData();
            formData.append('avatar', blob, 'avatar.jpg');
            if (original) {
                // Send the un-cropped original too, so the server can keep it in
                // the "photos on my page" album.
                formData.append('original', original, original.name || 'original.jpg');
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

            this.applyResult(payload.url ?? '');
            this.close();
        } catch (err) {
            this.setError(err.message || 'Ошибка сети. Попробуйте ещё раз.');
        } finally {
            this.uploading = false;
        }
    }

    applyResult(url) {
        if (!url) return;

        // Replace the fallback placeholder with the new avatar image.
        if (this.fallback) {
            this.fallback.remove();
            this.fallback = null;
        }

        if (!this.img) {
            this.img = document.createElement('img');
            this.img.className = 'profile-avatar-img';
            this.img.dataset.avatarImg = '';
            this.img.alt = 'Аватар';
            this.widget.insertBefore(this.img, this.widget.firstChild);
        }

        this.img.src = url;
        this.widget.dataset.currentAvatar = url;

        this.updateHeaderAvatars(url);
    }

    // Обновим аватар в шапке (кружок справа) и в выпадающем меню.
    updateHeaderAvatars(url) {
        const header = Dom.qs('[data-header-avatar]', document);
        if (header) {
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
        }

        const dropdown = Dom.qs('[data-header-dropdown-avatar]', document);
        if (dropdown) {
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
        }
    }
}
