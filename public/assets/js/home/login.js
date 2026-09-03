class LoginForm {
    constructor() {
        this.form = document.querySelector("#login-form");
        if (!this.form) return;

        this.init();
    }

    init() {
        this.form
            .querySelector(".btn-primary-login")
            .addEventListener("click", e => {
                e.preventDefault();
                this.submit();
            });

        this.form.querySelectorAll("input").forEach(input => {
            input.addEventListener("input", () => this.clearError(input));
        });
    }

    submit() {
        const payload = {
            email: this.value("email"),
            password: this.value("password"),
            remember: this.checked("remember"),
        };

        fetch("/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
            body: JSON.stringify(payload),
        })
            .then(async r => {
                const text = await r.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Не JSON:", text);
                    throw e;
                }
            })
            .then(res => this.handleResponse(res))
            .catch(err => {
                console.error(err);
                alert("Ошибка соединения (см. консоль)");
            });
    }

    handleResponse(res) {
        if (res.status === "validation_error") {
            this.renderErrors(res.errors);
            return;
        }

        if (res.status === "ok") {
            window.location.href = "/id" + res.uid;
        }
    }

    renderErrors(errors) {
        Object.entries(errors).forEach(([field, message]) => {
            const el = this.form.querySelector(`[name="${field}"]`);
            if (el) this.showError(el, message);
        });
    }

    showError(el, message) {
        this.clearError(el);

        el.classList.add("input-error");

        const error = document.createElement("div");
        error.className = "error-text";
        error.textContent = message;

        el.closest(".form-group")?.appendChild(error);
    }

    clearError(el) {
        el.classList.remove("input-error");
        el.closest(".form-group")?.querySelector(".error-text")?.remove();
    }

    value(name) {
        return this.form.querySelector(`[name="${name}"]`)?.value.trim() ?? "";
    }

    checked(name) {
        return this.form.querySelector(`[name="${name}"]`)?.checked ?? false;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new LoginForm();
});