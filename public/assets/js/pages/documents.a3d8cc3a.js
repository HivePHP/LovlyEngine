import Dom from '../core/Dom.3c5c29e1.js';
import Ajax from '../core/Ajax.ce7e7771.js';
import AjaxUpload from '../core/AjaxUpload.6f3609bc.js';
import { highlight, getLangFromExt } from '../core/SyntaxHighlight.3f0c1c78.js';

const EXT_MAP = {
    image: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'],
    archive: ['zip', 'rar', '7z', 'tar', 'gz'],
    document: ['pdf', 'doc', 'docx', 'txt', 'rtf', 'odt'],
    spreadsheet: ['xls', 'xlsx', 'csv'],
    music: ['mp3', 'wav', 'ogg', 'flac'],
    video: ['mp4', 'avi', 'mkv', 'mov', 'webm'],
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

function escapeHtml(s) {
    return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' Б';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' КБ';
    if (bytes < 1073741824) return (bytes / 1048576).toFixed(1) + ' МБ';
    return (bytes / 1073741824).toFixed(1) + ' ГБ';
}

const TEXT_EXTS = [
    'txt', 'js', 'jsx', 'ts', 'tsx', 'php', 'css', 'html', 'htm',
    'json', 'xml', 'yaml', 'yml', 'md', 'sql', 'py', 'rb', 'java',
    'c', 'cpp', 'h', 'cs', 'go', 'rs', 'sh', 'bash', 'bat',
    'ini', 'cfg', 'conf', 'env', 'log', 'csv', 'vue', 'svelte',
];

const PREVIEWABLE = [...TEXT_EXTS, 'zip', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp4', 'webm', 'mp3', 'wav', 'ogg'];

export default class DocumentsPage {
    constructor(root) {
        this.root = root;
        if (!root) return;

        this.isPublic = root.dataset.isPublic === '1';
        this.ownerId = root.dataset.ownerId || '';

        this.contentEl = Dom.qs('[data-docs-content]', root);
        this.listEl = Dom.qs('[data-docs-list]', root);
        this.progressEl = Dom.qs('[data-upload-progress]', root);
        this.input = Dom.qs('#docs-file-input', document);

        this.previewModal = Dom.qs('[data-preview-modal]', root);
        this.previewTitle = Dom.qs('[data-preview-title]', root);
        this.previewBody = Dom.qs('[data-preview-body]', root);
        this.previewDownload = Dom.qs('[data-preview-download]', root);

        this.currentPreviewId = null;

        this.bind();
    }

    bind() {
        if (!this.isPublic) {
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
        }

        this.listEl?.addEventListener('click', (e) => {
            if (!this.isPublic) {
                const delBtn = e.target.closest('[data-doc-delete]');
                if (delBtn) {
                    e.preventDefault();
                    this.deleteDoc(delBtn);
                    return;
                }
            }
            const previewBtn = e.target.closest('[data-doc-preview]');
            if (previewBtn) {
                e.preventDefault();
                this.previewDoc(previewBtn);
            }
        });

        this.previewModal?.querySelectorAll('[data-preview-close]').forEach(el => {
            el.addEventListener('click', () => this.closePreview());
        });

        this.previewModal?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) this.closePreview();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.previewModal && !this.previewModal.hidden) {
                this.closePreview();
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

    /* ── Preview ──────────────────────────────────────── */

    async previewDoc(btn) {
        const wrapper = btn.closest('[data-doc-id]');
        const id = wrapper?.getAttribute('data-doc-id');
        const name = wrapper?.getAttribute('data-doc-name') || '';
        const filePath = wrapper?.getAttribute('data-doc-path') || '';
        if (!id) return;

        this.currentPreviewId = id;

        const ext = getExt(name).toLowerCase();

        /* Image — open directly in lightbox */
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            this.openPreview(name, `<img class="preview-img" src="${escapeHtml(filePath)}" alt="${escapeHtml(name)}">`);
            return;
        }

        /* Video */
        if (['mp4', 'webm', 'mov'].includes(ext)) {
            this.openPreview(name, `<video class="preview-video" controls autoplay src="${escapeHtml(filePath)}"></video>`);
            return;
        }

        /* Audio */
        if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) {
            this.openPreview(name, `<div class="preview-audio-wrap"><audio class="preview-audio" controls autoplay src="${escapeHtml(filePath)}"></audio></div>`);
            return;
        }

        /* PDF */
        if (ext === 'pdf') {
            this.openPreview(name, `<iframe class="preview-pdf" src="${escapeHtml(filePath)}"></iframe>`);
            return;
        }

        /* Text / ZIP — fetch from API */
        if (PREVIEWABLE.includes(ext)) {
            this.openPreview(name, '<div class="preview-loading">Загрузка...</div>');

            try {
                const res = await fetch(`/api/documents/${encodeURIComponent(id)}/preview`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const data = await res.json();

                if (data.status !== 'ok') {
                    this.setPreviewBody(`<div class="preview-error">${escapeHtml(data.message || 'Ошибка')}</div>`);
                    return;
                }

                if (data.type === 'text') {
                    const lang = getLangFromExt(ext);
                    const highlighted = lang !== 'text' ? highlight(data.content, lang) : escapeHtml(data.content);
                    this.setPreviewBody(`<pre class="preview-code"><code>${highlighted}</code></pre>`);
                } else if (data.type === 'zip') {
                    this.renderZipListing(data.entries || []);
                } else if (data.type === 'file') {
                    window.open(data.url, '_blank');
                    this.closePreview();
                }
            } catch (_) {
                this.setPreviewBody('<div class="preview-error">Ошибка загрузки</div>');
            }
            return;
        }

        /* Fallback — just download */
        window.open(filePath, '_blank');
    }

    openPreview(title, bodyHtml) {
        if (!this.previewModal) return;
        if (this.previewTitle) this.previewTitle.textContent = title;
        if (this.previewBody) this.previewBody.innerHTML = bodyHtml;
        if (this.previewDownload && this.currentPreviewId) {
            this.previewDownload.href = `/api/documents/${this.currentPreviewId}/download`;
        }
        this.previewModal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    setPreviewBody(html) {
        if (this.previewBody) this.previewBody.innerHTML = html;
    }

    closePreview() {
        if (!this.previewModal) return;
        this.previewModal.hidden = true;
        if (this.previewBody) this.previewBody.innerHTML = '';
        document.body.style.overflow = '';
    }

    renderZipListing(entries) {
        if (!entries.length) {
            this.setPreviewBody('<div class="preview-empty">Архив пуст</div>');
            return;
        }

        let html = '<div class="preview-zip">';
        html += '<div class="preview-zip-header">';
        html += '<span class="preview-zip-col">Имя</span>';
        html += '<span class="preview-zip-col preview-zip-col--right">Размер</span>';
        html += '<span class="preview-zip-col preview-zip-col--right">Сжатый</span>';
        html += '</div>';
        html += '<div class="preview-zip-list">';

        for (const e of entries) {
            const isDir = e.is_dir || e.name.endsWith('/');
            const icon = isDir
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';

            html += '<div class="preview-zip-row">';
            html += `<span class="preview-zip-col preview-zip-name">${icon}<span>${escapeHtml(e.name)}</span></span>`;
            html += `<span class="preview-zip-col preview-zip-col--right">${isDir ? '—' : formatSize(e.size)}</span>`;
            html += `<span class="preview-zip-col preview-zip-col--right">${isDir ? '—' : formatSize(e.comp)}</span>`;
            html += '</div>';
        }

        html += '</div></div>';
        this.setPreviewBody(html);
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
