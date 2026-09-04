import Ajax from './Ajax.ce7e7771.js';

/**
 * AjaxUpload — reusable file upload with progress bar.
 *
 * Usage:
 *   const up = new AjaxUpload('/api/albums/1/photos', {
 *       fieldName: 'photos[]',
 *       files: fileList,
 *       accept: 'image/jpeg,image/png,image/webp,image/gif',
 *       maxFileSize: 8 * 1024 * 1024,
 *       multiple: true,
 *       progressTarget: document.querySelector('[data-upload-progress]'),
 *       onSuccess(data) { ... },
 *       onError(msg) { ... },
 *   });
 *   up.upload();
 *
 * Or with drag-and-drop:
 *   AjaxUpload.attachDropZone(dropEl, inputEl, { ... });
 */
export default class AjaxUpload {
    /**
     * @param {string} url           Upload endpoint.
     * @param {object} opts          Configuration.
     * @param {string}   opts.fieldName      FormData field name (default 'file').
     * @param {FileList|File[]} opts.files    Files to upload.
     * @param {string}   [opts.accept]       Comma-separated accepted MIME types.
     * @param {number}   [opts.maxFileSize]  Max bytes per file.
     * @param {boolean}  [opts.multiple]     Allow multiple files (default true).
     * @param {HTMLElement} [opts.progressTarget]  Container for the progress bar (auto-created if omitted).
     * @param {Function} [opts.onProgress]   Called with { loaded, total, percent }.
     * @param {Function} [opts.onSuccess]    Called with parsed JSON response.
     * @param {Function} [opts.onError]      Called with error message string.
     * @param {Function} [opts.onComplete]   Called after success or error (always).
     */
    constructor(url, opts = {}) {
        this.url = url;
        this.fieldName = opts.fieldName || 'file';
        this.files = opts.files || [];
        this.accept = opts.accept || '';
        this.maxFileSize = opts.maxFileSize || 0;
        this.multiple = opts.multiple !== false;
        this.progressTarget = opts.progressTarget || null;
        this.onProgress = opts.onProgress || null;
        this.onSuccess = opts.onSuccess || null;
        this.onError = opts.onError || null;
        this.onComplete = opts.onComplete || null;

        this._xhr = null;
        this._bar = null;
        this._text = null;
        this._fill = null;
        this._cancelled = false;
    }

    /**
     * Validate a FileList. Returns { valid: File[], errors: string[] }.
     */
    validate(files) {
        const valid = [];
        const errors = [];

        const types = this.accept
            ? this.accept.split(',').map(s => s.trim().toLowerCase())
            : [];

        for (const file of files) {
            if (types.length && !types.includes(file.type.toLowerCase())) {
                errors.push(`"${file.name}" — недопустимый формат`);
                continue;
            }
            if (this.maxFileSize && file.size > this.maxFileSize) {
                errors.push(`"${file.name}" — слишком большой (макс. ${this.formatSize(this.maxFileSize)})`);
                continue;
            }
            valid.push(file);
        }

        return { valid, errors };
    }

    /**
     * Execute the upload. Returns a Promise that resolves with the server JSON.
     */
    upload() {
        return new Promise((resolve, reject) => {
            const fileArr = Array.from(this.files);
            if (!fileArr.length) {
                const msg = 'Файлы не выбраны';
                this._fireError(msg);
                reject(new Error(msg));
                return;
            }

            const { valid, errors } = this.validate(fileArr);
            if (errors.length) {
                this._fireError(errors.join('\n'));
                reject(new Error(errors.join('\n')));
                return;
            }
            if (!valid.length) {
                const msg = 'Нет файлов для загрузки';
                this._fireError(msg);
                reject(new Error(msg));
                return;
            }

            this._cancelled = false;
            this._showProgress(valid.length);

            const fd = new FormData();
            for (const file of valid) {
                fd.append(this.fieldName, file, file.name);
            }
            fd.append('csrf_token', Ajax.csrfToken());

            const xhr = new XMLHttpRequest();
            this._xhr = xhr;

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable && this._fill) {
                    const pct = Math.round((e.loaded / e.total) * 100);
                    this._fill.style.width = pct + '%';
                    if (this._text) {
                        this._text.textContent = `${this.formatSize(e.loaded)} / ${this.formatSize(e.total)}`;
                    }
                    if (this.onProgress) {
                        this.onProgress({ loaded: e.loaded, total: e.total, percent: pct });
                    }
                }
            });

            xhr.addEventListener('load', () => {
                if (this._cancelled) return;
                this._completeBar();

                let data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (_) {
                    const msg = `Некорректный ответ сервера (HTTP ${xhr.status})`;
                    this._fireError(msg);
                    reject(new Error(msg));
                    return;
                }

                if (xhr.status >= 400 || data.status === 'error') {
                    const msg = data.message || `Ошибка HTTP ${xhr.status}`;
                    this._fireError(msg);
                    reject(new Error(msg));
                    return;
                }

                if (this.onSuccess) this.onSuccess(data);
                resolve(data);
            });

            xhr.addEventListener('error', () => {
                if (this._cancelled) return;
                this._completeBar();
                const msg = 'Ошибка сети';
                this._fireError(msg);
                reject(new Error(msg));
            });

            xhr.addEventListener('abort', () => {
                this._hideProgress();
                reject(new Error('Загрузка отменена'));
            });

            xhr.open('POST', this.url);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-Token', Ajax.csrfToken());
            xhr.withCredentials = true;
            xhr.send(fd);
        }).finally(() => {
            if (this.onComplete) this.onComplete();
        });
    }

    /** Cancel an in-progress upload. */
    cancel() {
        this._cancelled = true;
        if (this._xhr) this._xhr.abort();
    }

    // ── Progress bar UI ──────────────────────────────────────

    _showProgress(fileCount) {
        if (!this.progressTarget) return;

        this.progressTarget.textContent = '';

        const wrap = document.createElement('div');
        wrap.className = 'upload-progress';

        const header = document.createElement('div');
        header.className = 'upload-progress-header';

        const label = document.createElement('span');
        label.className = 'upload-progress-label';
        label.textContent = fileCount > 1
            ? `Загрузка ${fileCount} файлов…`
            : 'Загрузка файла…';

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'upload-progress-cancel';
        cancelBtn.textContent = 'Отмена';
        cancelBtn.addEventListener('click', () => this.cancel());

        header.appendChild(label);
        header.appendChild(cancelBtn);

        const track = document.createElement('div');
        track.className = 'upload-progress-track';

        const fill = document.createElement('div');
        fill.className = 'upload-progress-fill';
        track.appendChild(fill);

        const text = document.createElement('div');
        text.className = 'upload-progress-text';
        text.textContent = '0 B / 0 B';

        wrap.appendChild(header);
        wrap.appendChild(track);
        wrap.appendChild(text);

        this.progressTarget.appendChild(wrap);
        this._bar = wrap;
        this._fill = fill;
        this._text = text;
    }

    _completeBar() {
        if (this._fill) {
            this._fill.style.width = '100%';
            this._fill.classList.add('upload-progress-fill--done');
        }
        if (this._text) this._text.textContent = 'Готово';
        if (this._bar) {
            setTimeout(() => this._hideProgress(), 1200);
        }
    }

    _hideProgress() {
        if (this._bar) {
            this._bar.style.transition = 'opacity 0.25s ease';
            this._bar.style.opacity = '0';
            setTimeout(() => {
                if (this._bar && this._bar.parentNode) {
                    this._bar.parentNode.removeChild(this._bar);
                }
                this._bar = null;
                this._fill = null;
                this._text = null;
            }, 260);
        }
    }

    _fireError(msg) {
        if (this.onError) this.onError(msg);
        this._hideProgress();
    }

    // ── Helpers ──────────────────────────────────────────────

    formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
    }

    // ── Static: attach drag-and-drop to a zone ───────────────

    /**
     * Attach drag-and-drop handlers to a drop zone element.
     *
     * @param {HTMLElement} dropEl     The drop zone.
     * @param {HTMLInputElement} [inputEl]  Optional hidden file input to sync.
     * @param {object} opts           Same as AjaxUpload constructor opts, plus:
     * @param {Function} [opts.onFiles]  Called with FileList when files are dropped.
     */
    static attachDropZone(dropEl, inputEl, opts = {}) {
        if (!dropEl) return;

        const prevent = (e) => { e.preventDefault(); e.stopPropagation(); };

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev =>
            dropEl.addEventListener(ev, prevent)
        );

        ['dragenter', 'dragover'].forEach(ev =>
            dropEl.addEventListener(ev, () => dropEl.classList.add('is-dragover'))
        );

        ['dragleave', 'drop'].forEach(ev =>
            dropEl.addEventListener(ev, () => dropEl.classList.remove('is-dragover'))
        );

        dropEl.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (!files.length) return;
            if (inputEl) inputEl.files = files;
            if (opts.onFiles) opts.onFiles(files);
        });

        if (inputEl) {
            inputEl.addEventListener('change', () => {
                if (!inputEl.files.length) return;
                if (opts.onFiles) opts.onFiles(inputEl.files);
            });
        }
    }
}
