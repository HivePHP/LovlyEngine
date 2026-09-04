import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import AjaxUpload from '../core/AjaxUpload.6f3609bc.js';
import Modal from '../ui/Modal.c0ac6b17.js';
import DragReorder from '../ui/DragReorder.1c9f2b1a.js';

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_BYTES = 8 * 1024 * 1024;

/* ============================================================
   GENERIC CONFIRM MODAL (delete albums / photos)
   ============================================================ */
class ConfirmModal extends Modal {
    constructor(root) {
        super(root);
        if (!this.root) return;
        if (this.root.dataset.bound === '1') return;
        this.root.dataset.bound = '1';

        this.textEl = Dom.qs('[data-confirm-text]', this.root);
        this.yesBtn = Dom.qs('[data-confirm-yes]', this.root);
        this.pending = null;
        this.running = false;

        this.yesBtn?.addEventListener('click', () => this.confirm());
    }

    /**
     * @param {{title:string, text:string, url:string, onSuccess?:Function}} opts
     */
    ask(opts) {
        this.pending = opts;
        this.running = false;
        this.clearError();
        this.setTitle(opts.title);
        if (this.textEl) this.textEl.textContent = opts.text;
        this.open();
    }

    async confirm() {
        const p = this.pending;
        if (!p || this.running) return;
        this.running = true;
        this.clearError();

        try {
            const payload = await Ajax.post(p.url, {});
            if (payload.status !== 'ok') {
                throw new Error(payload.message || 'Не удалось выполнить операцию.');
            }
            this.close();
            if (typeof p.onSuccess === 'function') p.onSuccess();
            else window.location.reload();
        } catch (err) {
            this.setError(err.message || 'Ошибка сети. Попробуйте ещё раз.');
        } finally {
            this.running = false;
        }
    }
}

/* ============================================================
   CREATE ALBUM MODAL
   ============================================================ */
class CreateAlbumModal extends Modal {
    constructor(root) {
        super(root);
        if (!this.root) return;

        if (this.root.dataset.bound === '1') return;
        this.root.dataset.bound = '1';

        this.title = Dom.qs('#album-title', this.root);
        this.description = Dom.qs('#album-desc', this.root);
        this.saveBtn = Dom.qs('[data-album-save]', this.root);
        this.uploading = false;

        Dom.qsa('[data-create-album]', document).forEach((btn) => {
            btn.addEventListener('click', () => this.openAlbum());
        });

        this.onOpen = () => {
            this.clearError();
            setTimeout(() => this.title?.focus(), 50);
        };

        this.saveBtn.addEventListener('click', () => this.saveAlbum());
        this.title.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); this.saveAlbum(); }
        });
    }

    openAlbum() {
        if (this.title) this.title.value = '';
        if (this.description) this.description.value = '';
        this.clearError();
        this.open();
    }

    async saveAlbum() {
        if (this.uploading) return;
        const title = this.title ? this.title.value.trim() : '';
        const description = this.description ? this.description.value.trim() : '';

        if (!title) {
            this.setError('Введите название альбома.');
            this.title?.focus();
            return;
        }

        if (title.length > 255) {
            this.setError('Название слишком длинное.');
            return;
        }

        this.uploading = true;
        this.clearError();

        try {
            const payload = await Ajax.post('/api/albums/create', { title, description });
            if (payload.status !== 'ok') {
                throw new Error(payload.message || 'Не удалось создать альбом.');
            }
            this.close();
            const page = Dom.qs('[data-albums-page]', document);
            const ownerId = page?.dataset.userId;
            window.location.href = payload.url || (ownerId ? '/albums/id' + ownerId : '/');
        } catch (err) {
            this.setError(err.message || 'Ошибка сети. Попробуйте ещё раз.');
        } finally {
            this.uploading = false;
        }
    }
}

/* ============================================================
   ALBUMS LIST — reorder (pointer drag), delete album
   ============================================================ */
class AlbumsList {
    constructor(root, confirm) {
        this.root = root;
        if (this.root.dataset.reorderBound === '1') return;
        this.root.dataset.reorderBound = '1';

        this.confirm = confirm;

        const grid = Dom.qs('.albums-grid', this.root);
        if (this.root.dataset.isOwner !== '1' || !grid) {
            this.wireDelete();
            return;
        }

        this.reorder = new DragReorder(
            grid,
            '.album-card',
            (card) => card.dataset.albumId,
            (ids) => this.persist(ids)
        );

        this.wireDelete();
    }

    wireDelete() {
        const container = this.root;
        container.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-delete-album]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            const card = btn.closest('.album-card');
            const id = card?.dataset.albumId;
            if (!this.confirm || !id) return;
            this.confirm.ask({
                title: 'Удаление альбома',
                text: 'Вы действительно хотите удалить альбом? Все фотографии этого альбома будут удалены безвозвратно.',
                url: '/api/albums/' + id + '/delete',
            });
        });
    }

    async persist(ids) {
        try {
            const payload = await Ajax.post('/api/albums/reorder', { ids });
            if (payload.status !== 'ok') throw new Error(payload.message || 'Не удалось изменить порядок.');
        } catch (err) {
            window.location.reload();
        }
    }
}

/* ============================================================
   ALBUM PAGE — upload, delete, reorder photos, lightbox
   ============================================================ */
class AlbumPage {
    constructor(root, confirm) {
        this.root = root;
        if (this.root.dataset.pageBound === '1') return;
        this.root.dataset.pageBound = '1';

        this.confirm = confirm;
        this.albumId = this.root.dataset.albumId;
        this.isOwner = this.root.dataset.isOwner === '1';
        this.isProtected = this.root.dataset.protected === '1';

        this.input = Dom.qs('#album-photo-input', document);
        this.errorEl = Dom.qs('[data-upload-error]', this.root);
        this.lightbox = Dom.qs('[data-lightbox]', this.root);
        this.lightboxImage = Dom.qs('[data-lightbox-image]', this.root);
        this.uploading = false;

        if (!this.isProtected) {
            this.setupUpload();
        }
        this.setupLightbox();
        if (this.isOwner) {
            this.setupDelete();
            if (!this.isProtected) {
                this.setupPhotoReorder();
            }
        }
    }

    setError(message) {
        if (this.errorEl) this.errorEl.textContent = message || '';
    }

    /* ---- Upload ---- */
    setupUpload() {
        const triggers = Dom.qsa('[data-upload-trigger]', this.root);
        triggers.forEach((t) => t.addEventListener('click', (e) => {
            e.stopPropagation();
            this.input?.click();
        }));

        const area = Dom.qs('[data-upload-area]', this.root);
        const progressTarget = Dom.qs('[data-upload-progress]', this.root);

        if (this.isOwner && area) {
            AjaxUpload.attachDropZone(area, this.input, {
                onFiles: (files) => {
                    const imageFiles = Array.from(files).filter(f => f.type.startsWith('image/'));
                    if (imageFiles.length) this.uploadPhotos(imageFiles);
                },
            });
        }

        this.input?.addEventListener('change', () => {
            const files = [...this.input.files];
            if (files.length) this.uploadPhotos(files);
            this.input.value = '';
        });
    }

    uploadPhotos(files) {
        if (this.uploading) return;
        this.uploading = true;
        this.setError('');

        const progressTarget = Dom.qs('[data-upload-progress]', this.root);

        const up = new AjaxUpload(`/api/albums/${this.albumId}/photos`, {
            fieldName: 'photos[]',
            files,
            accept: ALLOWED_TYPES.join(','),
            maxFileSize: MAX_BYTES,
            multiple: true,
            progressTarget,
            onSuccess: () => {
                window.location.reload();
            },
            onError: (msg) => {
                this.setError(msg);
            },
            onComplete: () => {
                this.uploading = false;
            },
        });

        up.upload();
    }

    /* ---- Delete photo (confirm modal) ---- */
    setupDelete() {
        Dom.qs('[data-photos-grid]', this.root)?.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-delete-photo]');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            const photo = btn.closest('[data-photo-id]');
            const id = photo?.dataset.photoId;
            if (!this.confirm || !id) return;

            const text = this.isProtected
                ? 'Если это текущая фотография профиля, аватар сбросится на предыдущий (или на заглушку), а её оригинал исчезнет. Продолжить?'
                : 'Вы действительно хотите удалить фотографию?';

            this.confirm.ask({
                title: 'Удаление фотографии',
                text,
                url: '/api/photos/' + id + '/delete',
            });
        });
    }

    /* ---- Photo reorder (pointer drag) ---- */
    setupPhotoReorder() {
        const grid = Dom.qs('[data-photos-grid]', this.root);
        if (!grid) return;

        new DragReorder(
            grid,
            '.photo',
            (photo) => photo.dataset.photoId,
            (ids) => this.persistPhotoOrder(ids)
        );
    }

    async persistPhotoOrder(ids) {
        try {
            const payload = await Ajax.post(`/api/albums/${this.albumId}/photos/reorder`, { ids });
            if (payload.status !== 'ok') throw new Error(payload.message || 'Не удалось изменить порядок.');
        } catch (err) {
            this.setError(err.message || 'Ошибка сети. Попробуйте ещё раз.');
            window.location.reload();
        }
    }

    /* ---- Lightbox ---- */
    setupLightbox() {
        Dom.qs('[data-photos-grid]', this.root)?.addEventListener('click', (e) => {
            const img = e.target.closest('.photo-open');
            if (!img) return;
            const src = img.dataset.photoFull || img.src;
            this.openLightbox(src);
        });

        Dom.qs('[data-lightbox-close]', this.root)?.addEventListener('click', () => this.closeLightbox());

        this.lightbox?.addEventListener('click', (e) => {
            if (e.target === this.lightbox) this.closeLightbox();
        });
    }

    openLightbox(src) {
        if (!this.lightbox) return;
        if (this.lightboxImage) this.lightboxImage.src = src;
        this.lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    closeLightbox() {
        if (!this.lightbox) return;
        this.lightbox.hidden = true;
        if (this.lightboxImage) this.lightboxImage.src = '';
        document.body.style.overflow = '';
    }
}

/* ============================================================
   PAGE INIT (re-mounted by SPA router)
   ============================================================ */
export function init() {
    const albumsList = Dom.qs('[data-albums-page]');
    const albumPage = Dom.qs('[data-album-page]');

    const confirmEl = Dom.qs('[data-confirm-modal]');
    const confirm = confirmEl ? new ConfirmModal(confirmEl) : null;

    if (albumsList) {
        new CreateAlbumModal(Dom.qs('[data-album-modal]', albumsList));
        new AlbumsList(albumsList, confirm);
    }

    if (albumPage) {
        new AlbumPage(albumPage, confirm);

        if (albumPage.dataset.escBound !== '1') {
            albumPage.dataset.escBound = '1';
            document.addEventListener('keydown', function onKey(e) {
                if (e.key === 'Escape') {
                    const lb = Dom.qs('[data-lightbox]', albumPage);
                    if (lb && !lb.hidden) {
                        lb.hidden = true;
                        const img = Dom.qs('[data-lightbox-image]', lb);
                        if (img) img.src = '';
                        document.body.style.overflow = '';
                    }
                }
            });
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
