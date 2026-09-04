import Dom from '../core/Dom.js';

/**
 * DragReorder — переносимый класс перетаскивания для переупорядочивания
 * элементов сетки/списка (мышь + тач).
 *
 * Поведение:
 *   - по зажатию мыши и сдвигу за порог элемент помечается классом
 *     `dragging` (синяя рамка);
 *   - элемент под курсором подсвечивается классом `drag-over`;
 *   - перетаскиваемый элемент вставляется рядом с целью на лету;
 *   - на отпускании вызывается `onReorder(ids)` с итоговым порядком id;
 *   - клик (без сдвига) не перехватывается и работает как обычно.
 *
 * Использование:
 *   new DragReorder(gridEl, '.card', (el) => el.dataset.id, (ids) => {...}, {
 *       threshold: 6,
 *       axis: 'auto',            // 'auto' | 'x' | 'y'
 *       draggingClass: 'dragging',
 *       dragOverClass: 'drag-over',
 *       disabledSelector: '[data-delete],button', // не начинать перетаскивание
 *   });
 */
export default class DragReorder {
    constructor(grid, itemSelector, getId, onReorder, options = {}) {
        this.grid = grid;
        this.sel = itemSelector;
        this.getId = getId;
        this.onReorder = onReorder;

        this.threshold = options.threshold ?? 6;
        this.axis = options.axis ?? 'auto';
        this.draggingClass = options.draggingClass ?? 'dragging';
        this.dragOverClass = options.dragOverClass ?? 'drag-over';
        this.disabledSelector = options.disabledSelector ?? '[data-delete-album],[data-delete-photo],button';

        this.dragged = null;
        this.active = false;
        this.startX = 0;
        this.startY = 0;

        this.onPointerDown = this.onPointerDown.bind(this);
        this.onPointerMove = this.onPointerMove.bind(this);
        this.onPointerUp = this.onPointerUp.bind(this);

        this.grid.addEventListener('mousedown', this.onPointerDown);
        this.grid.addEventListener('touchstart', this.onPointerDown, { passive: false });
        document.addEventListener('mousemove', this.onPointerMove);
        document.addEventListener('touchmove', this.onPointerMove, { passive: false });
        document.addEventListener('mouseup', this.onPointerUp);
        document.addEventListener('touchend', this.onPointerUp);
        document.addEventListener('touchcancel', this.onPointerUp);
    }

    onPointerDown(e) {
        if (e.type === 'mousedown' && e.button !== 0) return;
        const item = e.target.closest(this.sel);
        if (!item) return;

        // Не начинать перетаскивание с интерактивных контролов (кнопки удаления...).
        if (this.disabledSelector && e.target.closest(this.disabledSelector)) return;

        this.dragged = item;
        this.startX = e.clientX;
        this.startY = e.clientY;
        this.active = false;
    }

    onPointerMove(e) {
        if (!this.dragged) return;
        const dx = e.clientX - this.startX;
        const dy = e.clientY - this.startY;

        if (!this.active) {
            let moved = false;
            if (this.axis === 'x') moved = Math.abs(dx) > this.threshold;
            else if (this.axis === 'y') moved = Math.abs(dy) > this.threshold;
            else moved = Math.hypot(dx, dy) > this.threshold;

            if (moved) {
                this.active = true;
                this.dragged.classList.add(this.draggingClass);
            }
        }

        if (!this.active) return;
        e.preventDefault();
        this.liveReorder(e.clientX, e.clientY, dx, dy);
    }

    onPointerUp() {
        if (!this.dragged) return;
        const wasActive = this.active;
        if (this.active) this.dragged.classList.remove(this.draggingClass);
        this.clearDragOver();

        const ids = [...this.grid.children]
            .filter((el) => el.matches(this.sel))
            .map((el) => this.getId(el));

        this.dragged = null;
        this.active = false;

        if (wasActive) {
            this.eatNextClick();
            if (this.onReorder) this.onReorder(ids);
        }
    }

    liveReorder(x, y, dx, dy) {
        this.clearDragOver();
        const target = document.elementFromPoint(x, y)?.closest(this.sel);
        if (!target || target === this.dragged) return;

        const rect = target.getBoundingClientRect();
        const horizontal = Math.abs(dx) > Math.abs(dy);
        const before = horizontal
            ? x < rect.left + rect.width / 2
            : y < rect.top + rect.height / 2;

        target.classList.add(this.dragOverClass);
        if (before) this.grid.insertBefore(this.dragged, target);
        else this.grid.insertBefore(this.dragged, target.nextSibling);
    }

    clearDragOver() {
        Dom.qsa('.' + this.dragOverClass, this.grid)
            .forEach((el) => el.classList.remove(this.dragOverClass));
    }

    eatNextClick() {
        const handler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            document.removeEventListener('click', handler, true);
        };
        document.addEventListener('click', handler, true);
        setTimeout(() => document.removeEventListener('click', handler, true), 350);
    }

    destroy() {
        this.grid.removeEventListener('mousedown', this.onPointerDown);
        this.grid.removeEventListener('touchstart', this.onPointerDown);
        document.removeEventListener('mousemove', this.onPointerMove);
        document.removeEventListener('touchmove', this.onPointerMove);
        document.removeEventListener('mouseup', this.onPointerUp);
        document.removeEventListener('touchend', this.onPointerUp);
        document.removeEventListener('touchcancel', this.onPointerUp);
    }
}
