/**
 * AvatarCrop — кроп аватарки, адаптированный под референс-механику.
 *
 * ИЗОБРАЖЕНИЕ РИСУЕТСЯ НА <canvas> В РЕЖИМЕ cover: заполняет всю область
 * фрейма (масштаб = max(frameW/natW, frameH/natH)), центрируется. Именно
 * canvas является источником истины для обрезки — координаты рамки считаются
 * от реальной отрисовки, поэтому результат ВСЕГДА совпадает с рамкой.
 *
 * РАМКА КРОПА — обычный <div>, позиционируемый через cropData (x,y,w,h):
 *   - перетаскивание мышью (pointer events + setPointerCapture)
 *   - изменение размера за 4 маркера (углы), минимальный размер, ограничение
 *     рамки в пределах фрейма
 *   - квадрат 1:1 (аватары квадратные; бэкенд режет в 512x512)
 *
 * ПУБЛИЧНЫЙ API (сохранён для интеграции с AvatarWidget):
 *   constructor(img)
 *   layout()        — нарисовать canvas, поставить рамку, подписать обработчики
 *   crop(size)      -> Promise<Blob> — выбранная область, size x size JPEG
 *   endDrag()       — сбросить состояние перетаскивания
 */
export default class AvatarCrop {
    static MIN = 80;

    constructor(img) {
        this.img = img;
        this.frame = img.closest('.avatar-crop-frame');
        this.wrap = this.frame.closest('.avatar-crop-wrap');

        this.canvas = this.frame.querySelector('[data-avatar-crop-canvas]')
                      || this.buildCanvas();
        this.ctx = this.canvas.getContext('2d');

        this.box = this.frame.querySelector('[data-avatar-crop-box]')
                   || this.buildBox();
        this.sizeLabel = this.frame.querySelector('[data-avatar-crop-size]');
        this.shades = this.buildShades();

        this.previews = [
            this.wrap.querySelector('[data-avatar-preview="100"]'),
            this.wrap.querySelector('[data-avatar-preview="50"]'),
        ].filter(Boolean);

        this.cropData = { x: 0, y: 0, width: 0, height: 0 };
        this.imageLoaded = !!(this.img && this.img.naturalWidth > 0);

        this.dragging = false;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.cropStartX = 0;
        this.cropStartY = 0;

        this.resizing = false;
        this.resizeHandle = null;
        this.resizeStartX = 0;
        this.resizeStartY = 0;
        this.originalCrop = null;
    }

    /* ------------------------------------------------------------------ */
    /*  DOM построение                                                     */
    /* ------------------------------------------------------------------ */
    buildCanvas() {
        const c = document.createElement('canvas');
        c.className = 'avatar-crop-canvas';
        c.dataset.avatarCropCanvas = '';
        this.frame.appendChild(c);
        return c;
    }

    buildBox() {
        const box = document.createElement('div');
        box.className = 'avatar-crop-box';
        box.dataset.avatarCropBox = '';
        box.innerHTML = `
            <span class="avatar-crop-handle avatar-crop-handle--tl" data-handle="top-left"></span>
            <span class="avatar-crop-handle avatar-crop-handle--tr" data-handle="top-right"></span>
            <span class="avatar-crop-handle avatar-crop-handle--bl" data-handle="bottom-left"></span>
            <span class="avatar-crop-handle avatar-crop-handle--br" data-handle="bottom-right"></span>`;
        this.frame.appendChild(box);
        return box;
    }

    buildShades() {
        const dirs = ['top', 'right', 'bottom', 'left'];
        return dirs.map((dir) => {
            let el = this.frame.querySelector(`[data-avatar-shade="${dir}"]`);
            if (!el) {
                el = document.createElement('div');
                el.className = `avatar-crop-shade avatar-crop-shade--${dir}`;
                el.dataset.avatarShade = dir;
                this.frame.appendChild(el);
            }
            return el;
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Геометрия (доступные размеры)                                      */
    /* ------------------------------------------------------------------ */
    area() {
        const r = this.frame.getBoundingClientRect();
        return { width: r.width, height: r.height };
    }

    /* ------------------------------------------------------------------ */
    /*  Отрисовка изображения (cover)                                      */
    /* ------------------------------------------------------------------ */
    drawImage() {
        const a = this.area();
        this.canvas.width = a.width;
        this.canvas.height = a.height;
        this.ctx.clearRect(0, 0, a.width, a.height);

        if (!this.imageLoaded || !this.img) return;

        const natW = this.img.naturalWidth || 1;
        const natH = this.img.naturalHeight || 1;
        const scale = Math.max(a.width / natW, a.height / natH);
        const drawW = natW * scale;
        const drawH = natH * scale;
        const x = (a.width - drawW) / 2;
        const y = (a.height - drawH) / 2;
        this.drawX = x;
        this.drawY = y;
        this.drawScale = scale;
        this.ctx.drawImage(this.img, x, y, drawW, drawH);
    }

    /* ------------------------------------------------------------------ */
    /*  layout — инициализация                                             */
    /* ------------------------------------------------------------------ */
    layout() {
        this.imageLoaded = !!(this.img && this.img.naturalWidth > 0);
        this.drawImage();

        const a = this.area();
        /* Дефолтный квадрат: ~72% от меньшей стороны фрейма, но не больше площади. */
        const size = Math.floor(Math.min(a.width, a.height) * 0.72);
        this.cropData = {
            x: Math.floor((a.width - size) / 2),
            y: Math.floor((a.height - size) / 2),
            width: size,
            height: size,
        };

        this.frame.classList.add('is-cropping');
        this.bindEvents();
        this.box.hidden = false;
        this.box.classList.add('avatar-crop-box--active');
        if (this.sizeLabel) this.sizeLabel.hidden = false;
        this.updateCrop();
    }

    /* ------------------------------------------------------------------ */
    /*  События                                                            */
    /* ------------------------------------------------------------------ */
    bindEvents() {
        if (this._bound) return;
        this._bound = true;

        this.box.addEventListener('pointerdown', (e) => {
            if (!this.imageLoaded) return;
            const handle = e.target.dataset.handle;
            if (handle) { this.startResize(e, handle); return; }
            this.startDrag(e);
        });

        this.box.addEventListener('pointermove', (e) => {
            if (this.dragging) this.moveCrop(e);
            if (this.resizing) this.resizeCrop(e);
        });

        const onUp = () => {
            this.dragging = false;
            this.resizing = false;
            this.resizeHandle = null;
            try { this.box.releasePointerCapture(); } catch (_) {}
        };
        this.box.addEventListener('pointerup', onUp);
        this.box.addEventListener('pointercancel', onUp);
    }

    /* Значение рамки на экране — прямо из DOM, чтобы не было расхождений. */
    currentRect() {
        const r = this.box.getBoundingClientRect();
        const f = this.frame.getBoundingClientRect();
        return {
            x: r.left - f.left,
            y: r.top - f.top,
            width: r.width,
            height: r.height,
        };
    }

    setCrop(x, y, w, h) {
        this.cropData.x = x;
        this.cropData.y = y;
        this.cropData.width = w;
        this.cropData.height = h;
        this.updateCrop();
    }

    updateCrop() {
        const d = this.cropData;
        this.box.style.left = `${d.x}px`;
        this.box.style.top = `${d.y}px`;
        this.box.style.width = `${d.width}px`;
        this.box.style.height = `${d.height}px`;
        if (this.sizeLabel) {
            this.sizeLabel.textContent = `${Math.round(d.width)}×${Math.round(d.height)}`;
            this.sizeLabel.style.left = `${d.x + d.width + 12}px`;
            this.sizeLabel.style.top = `${d.y}px`;
        }
        this.updateShades(d);
        this.updatePreview();
    }

    /* ------------------------------------------------------------------ */
    /*  Превью (100x100 и 50x50) — рисуем выбранную область               */
    /* ------------------------------------------------------------------ */
    updatePreview() {
        if (!this.imageLoaded || !this.previews.length) return;
        const d = this.cropData;
        const scale = this.drawScale || 1;
        const sourceX = (d.x - this.drawX) / scale;
        const sourceY = (d.y - this.drawY) / scale;
        const sourceSize = d.width / scale;
        if (sourceSize <= 0) return;

        this.previews.forEach((cv) => {
            const ctx = cv.getContext('2d');
            const s = cv.width;
            ctx.clearRect(0, 0, s, s);
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(this.img, sourceX, sourceY, sourceSize, sourceSize, 0, 0, s, s);
        });
    }

    /* Обновление затемняющих панелей (верх/низ/лево/право) вокруг рамки. */
    updateShades(d) {
        const a = this.area();
        if (!this.shades || this.shades.length !== 4) return;
        this.shades[0].style.cssText = `top:0; left:0; width:${a.width}px; height:${d.y}px;`;
        this.shades[1].style.cssText = `top:${d.y}px; left:${d.x + d.width}px; width:${Math.max(0, a.width - d.x - d.width)}px; height:${d.height}px;`;
        this.shades[2].style.cssText = `top:${d.y + d.height}px; left:0; width:${a.width}px; height:${Math.max(0, a.height - d.y - d.height)}px;`;
        this.shades[3].style.cssText = `top:${d.y}px; left:0; width:${Math.max(0, d.x)}px; height:${d.height}px;`;
    }

    /* ------------------------------------------------------------------ */
    /*  Перетаскивание                                                     */
    /* ------------------------------------------------------------------ */
    startDrag(e) {
        this.dragging = true;
        this.dragStartX = e.clientX;
        this.dragStartY = e.clientY;
        this.cropStartX = this.cropData.x;
        this.cropStartY = this.cropData.y;
        try { this.box.setPointerCapture(e.pointerId); } catch (_) {}
    }

    moveCrop(e) {
        const a = this.area();
        const dx = e.clientX - this.dragStartX;
        const dy = e.clientY - this.dragStartY;
        let x = this.cropStartX + dx;
        let y = this.cropStartY + dy;
        x = Math.max(0, Math.min(x, a.width - this.cropData.width));
        y = Math.max(0, Math.min(y, a.height - this.cropData.height));
        this.setCrop(x, y, this.cropData.width, this.cropData.height);
    }

    /* ------------------------------------------------------------------ */
    /*  Изменение размера (квадрат)                                        */
    /* ------------------------------------------------------------------ */
    startResize(e, handle) {
        this.resizing = true;
        this.resizeHandle = handle;
        this.resizeStartX = e.clientX;
        this.resizeStartY = e.clientY;
        this.originalCrop = { ...this.cropData };
        try { this.box.setPointerCapture(e.pointerId); } catch (_) {}
    }

    resizeCrop(e) {
        const a = this.area();
        const dx = e.clientX - this.resizeStartX;
        const dy = e.clientY - this.resizeStartY;
        const MIN = AvatarCrop.MIN;
        const h = this.resizeHandle;

        /* Определяем новый размер и якорную позицию (противоположный угол). */
        let x = this.originalCrop.x;
        let y = this.originalCrop.y;
        let size = this.originalCrop.width; // квадрат

        if (h === 'bottom-right') {
            size = this.originalCrop.width + Math.max(dx, dy);
        } else if (h === 'top-left') {
            size = this.originalCrop.width - Math.max(dx, dy);
        } else if (h === 'top-right') {
            size = this.originalCrop.width + Math.max(dx, -dy);
        } else if (h === 'bottom-left') {
            size = this.originalCrop.width + Math.max(-dx, dy);
        }

        size = Math.max(MIN, size);

        /* Якорная точка (фиксируем противоположный угол). */
        const ax = this.originalCrop.x;
        const ay = this.originalCrop.y;
        const ax2 = this.originalCrop.x + this.originalCrop.width;
        const ay2 = this.originalCrop.y + this.originalCrop.height;

        if (h === 'top-left') { x = ax2 - size; y = ay2 - size; }
        else if (h === 'top-right') { y = ay2 - size; }
        else if (h === 'bottom-left') { x = ax2 - size; }
        /* bottom-right: x=ax, y=ay */

        /* Ограничение в рамках фрейма. */
        x = Math.max(0, Math.min(x, a.width));
        y = Math.max(0, Math.min(y, a.height));
        const maxSizeW = a.width - x;
        const maxSizeH = a.height - y;
        size = Math.max(MIN, Math.min(size, maxSizeW, maxSizeH));

        this.setCrop(x, y, size, size);
    }

    /* ------------------------------------------------------------------ */
    /*  Сброс (вызывается AvatarWidget при закрытии)                       */
    /* ------------------------------------------------------------------ */
    endDrag() {
        this.dragging = false;
        this.resizing = false;
        this.resizeHandle = null;
    }

    /* ------------------------------------------------------------------ */
    /*  Обрезка выбранной области -> JPEG Blob                             */
    /* ------------------------------------------------------------------ */
    crop(target = 512) {
        const d = this.cropData;
        const a = this.area();

        if (!this.imageLoaded || !d.width || !d.height) {
            return Promise.reject(new Error('Изображение не загружено'));
        }

        /* Координаты рамки -> координаты источника (превью/сохранение). */
        const scale = this.drawScale || 1;
        const sourceX = (d.x - this.drawX) / scale;
        const sourceY = (d.y - this.drawY) / scale;
        const sourceSize = d.width / scale;

        const out = document.createElement('canvas');
        out.width = target;
        out.height = target;
        const octx = out.getContext('2d');
        if (!octx) return Promise.reject(new Error('Canvas unavailable'));

        octx.imageSmoothingEnabled = true;
        octx.imageSmoothingQuality = 'high';
        octx.drawImage(this.img, sourceX, sourceY, sourceSize, sourceSize, 0, 0, target, target);

        return new Promise((resolve, reject) => {
            out.toBlob((blob) => blob ? resolve(blob) : reject(new Error('Canvas toBlob failed')), 'image/jpeg', 0.88);
        });
    }
}
