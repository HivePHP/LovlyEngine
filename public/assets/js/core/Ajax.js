export default class Ajax {
    static csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    static async post(url, data, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': Ajax.csrfToken(),
            ...(options.headers || {}),
        };

        const response = await fetch(url, {
            method: 'POST',
            headers,
            body: JSON.stringify(data),
            ...options,
        });

        let payload;
        const text = await response.text();
        try {
            payload = JSON.parse(text);
        } catch (e) {
            throw new Error(`Некорректный ответ сервера (HTTP ${response.status})`);
        }

        return payload;
    }
}
