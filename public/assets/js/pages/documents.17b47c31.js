import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import AjaxUpload from '../core/AjaxUpload.6f3609bc.js';

const EXT_MAP = {
    image: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
    archive: ['zip', 'rar', '7z', 'tar', 'gz'],
    document: ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'],
    spreadsheet: ['xls', 'xlsx', 'csv'],
    music: ['mp3', 'wav', 'ogg', 'flac'],
    video: ['mp4', 'avi', 'mkv', 'mov', 'webm'],
};

const ICONS = {
    image: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1.5"/><path d="m4 17 5-5 3 3 2-2 6 6"/></svg>',
    archive: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7h16v13H4V7Z"/><path d="M3 4h18v3H3V4Z"/><path d="M10 11h4"/></svg>',
    document: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6V3Z"/><path d="M14 3v5h4"/><path d="M9 12h6"/><path d="M9 16h6"/></svg>',
    spreadsheet: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h8"/><path d="M12 7v10"/></svg>',
    music: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V6l10-2v12"/><circle cx="6" cy="18" r="3"/><circle cx="16" cy="16" r="3"/></svg>',
    video: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m10 9 5 3-5 3V9Z"/></svg>',
    default: '<svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6V3Z"/><path d="M14 3v5h4"/><path d="M9 12h6"/><path d="M9 16h6"/></svg>',
};

function getExt(name) {
    const parts = name.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

function getFileType(ext) {
    for (const [type, exts] of Object.entries(EXT_MAP)) {
        if (exts.includes(ext)) return type;
    }
    return 'default';
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' Б';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' КБ';
    if (bytes < 1073741824) return (bytes / 1048576).toFixed(1) + ' МБ';
    return (bytes / 1073741824).toFixed(1) + ' ГБ';
}

function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

export default class DocumentsPage {
    constructor(root) {
        this.root = root;
        if (!root) return;

        this.contentEl = Dom.qs('[data-docs-content]', root);
        this.listEl = Dom.qs('[data-docs-list]', root);
        this.progressEl = Dom.qs('[data-upload-progress]', root);
        this.input = Dom.qs('#docs-file-input', document);

        this.bind();
    }

    bind() {
        Dom.qsa('[data-upload-trigger]', this.root).forEach(btn => {
            btn.addEventListener('click', () => this.input?.click());
        });

        this.input?.addEventListener('change', () => {
            if (this.input.files.length) {
                this.uploadFiles(this.input.files);
                this.input.value = '';
            }
        });

        const dropzone = Dom.qs('[data-dropzone]', this.root);
        if (dropzone) {
            AjaxUpload.attachDropZone(dropzone, this.input, {
                onFiles: (files) => this.uploadFiles(files),
            });
        }

        this.listEl?.addEventListener('click', (e) => {
            const delBtn = e.target.closest('[data-doc-delete]');
            if (delBtn) {
                e.preventDefault();
                this.deleteDoc(delBtn);
            }
        });
    }

    uploadFiles(fileList) {
        const up = new AjaxUpload('/api/documents/upload', {
            fieldName: 'files[]',
            files: fileList,
            maxFileSize: 50 * 1024 * 1024,
            multiple: true,
            progressTarget: this.progressEl,
            onSuccess: () => {
                window.location.reload();
            },
            onError: (msg) => {
                console.error('Upload error:', msg);
            },
        });
        up.upload();
    }

    async deleteDoc(btn) {
        const wrapper = btn.closest('[data-doc-id]');
        const id = wrapper?.getAttribute('data-doc-id');
        if (!id) return;

        const name = wrapper.querySelector('.doc-name')?.textContent || '';
        if (!confirm(`Удалить файл «${name}»?`)) return;

        try {
            const data = await Ajax.post(`/api/documents/${encodeURIComponent(id)}/delete`, {});
            if (data.status === 'ok') {
                wrapper.style.transition = 'opacity 0.2s ease';
                wrapper.style.opacity = '0';
                setTimeout(() => {
                    wrapper.remove();
                    if (!this.listEl?.querySelector('[data-doc-id]')) {
                        window.location.reload();
                    }
                }, 200);
            }
        } catch (_) {}
    }
}

/* ============================================================
   PAGE INIT
   ============================================================ */
export function init() {
    const root = Dom.qs('[data-docs-page]');
    if (!root) return;
    if (root.dataset.bound === '1') return;
    root.dataset.bound = '1';
    new DocumentsPage(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
