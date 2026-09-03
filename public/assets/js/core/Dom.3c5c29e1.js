export default class Dom {
    static qs(sel, root = document) {
        return root.querySelector(sel);
    }

    static qsa(sel, root = document) {
        return [...root.querySelectorAll(sel)];
    }
}
