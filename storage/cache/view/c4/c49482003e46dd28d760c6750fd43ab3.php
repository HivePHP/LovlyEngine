<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Sandbox\SecurityNotAllowedTestError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* profile/edit.twig */
class __TwigTemplate_13c508c9a92adea52a7b51c43ab1dfa7 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "layouts/main.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layouts/main.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 4
        yield "<div class=\"edit-profile-layout\">

    <aside class=\"profile-sidebar\">
        <div class=\"profile-avatar-card\">
            <div class=\"profile-avatar\">
                <svg width=\"80\" height=\"80\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
            </div>
            <div class=\"profile-avatar-name\">";
        // line 11
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 11, $this->source); })()), "html", null, true);
        yield " ";
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["surname"]) || array_key_exists("surname", $context) ? $context["surname"] : (function () { throw new RuntimeError('Variable "surname" does not exist.', 11, $this->source); })()), "html", null, true);
        yield "</div>
        </div>

        <nav class=\"profile-nav\">
            <a href=\"/id";
        // line 15
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 15, $this->source); })()), "html", null, true);
        yield "\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                Моя страница
            </a>
            <a href=\"/editprofile\" class=\"profile-nav-item active\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>
                Редактировать
            </a>
        </nav>
    </aside>

    <div class=\"profile-main\">
        <div class=\"profile-card\">
            <h2 class=\"edit-title\">Редактирование профиля</h2>

            <div id=\"success-message\" class=\"edit-success\" style=\"display:none;\">
                Изменения сохранены!
            </div>

            <form id=\"edit-profile-form\">
                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"about\">Обо мне</label>
                    <textarea
                        id=\"about\"
                        name=\"about\"
                        class=\"edit-textarea\"
                        placeholder=\"Расскажите о себе...\"
                        rows=\"4\"
                    >";
        // line 43
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 43, $this->source); })()), "html", null, true);
        yield "</textarea>
                </div>

                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"interests\">Интересы</label>
                    <textarea
                        id=\"interests\"
                        name=\"interests\"
                        class=\"edit-textarea\"
                        placeholder=\"Ваши увлечения и интересы...\"
                        rows=\"4\"
                    >";
        // line 54
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 54, $this->source); })()), "html", null, true);
        yield "</textarea>
                </div>

                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"favorite_films\">Любимые фильмы</label>
                    <textarea
                        id=\"favorite_films\"
                        name=\"favorite_films\"
                        class=\"edit-textarea\"
                        placeholder=\"Список любимых фильмов...\"
                        rows=\"4\"
                    >";
        // line 65
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 65, $this->source); })()), "html", null, true);
        yield "</textarea>
                </div>

                <div class=\"edit-actions\">
                    <button type=\"submit\" class=\"btn-primary edit-save-btn\" id=\"save-btn\">
                        Сохранить
                    </button>
                    <a href=\"/id";
        // line 72
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 72, $this->source); })()), "html", null, true);
        yield "\" class=\"btn-secondary edit-cancel-btn\">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener(\x27DOMContentLoaded\x27, function() {
    const form = document.getElementById(\x27edit-profile-form\x27);
    const saveBtn = document.getElementById(\x27save-btn\x27);
    const successMsg = document.getElementById(\x27success-message\x27);

    form.addEventListener(\x27submit\x27, function(e) {
        e.preventDefault();

        const data = {
            about: document.getElementById(\x27about\x27).value,
            interests: document.getElementById(\x27interests\x27).value,
            favorite_films: document.getElementById(\x27favorite_films\x27).value
        };

        saveBtn.disabled = true;
        saveBtn.textContent = \x27Сохранение...\x27;

        fetch(\x27/api/profile/update\x27, {
            method: \x27POST\x27,
            headers: {
                \x27Content-Type\x27: \x27application/json\x27,
                \x27X-Requested-With\x27: \x27XMLHttpRequest\x27
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === \x27ok\x27) {
                successMsg.style.display = \x27block\x27;
                setTimeout(() => {
                    successMsg.style.display = \x27none\x27;
                }, 3000);
            } else {
                alert(\x27Ошибка сохранения\x27);
            }
        })
        .catch(() => {
            alert(\x27Ошибка сети\x27);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = \x27Сохранить\x27;
        });
    });
});
</script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "profile/edit.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  146 => 72,  136 => 65,  122 => 54,  108 => 43,  77 => 15,  68 => 11,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/main.twig\" %}

{% block content %}
<div class=\"edit-profile-layout\">

    <aside class=\"profile-sidebar\">
        <div class=\"profile-avatar-card\">
            <div class=\"profile-avatar\">
                <svg width=\"80\" height=\"80\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
            </div>
            <div class=\"profile-avatar-name\">{{ name }} {{ surname }}</div>
        </div>

        <nav class=\"profile-nav\">
            <a href=\"/id{{ user_id }}\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                Моя страница
            </a>
            <a href=\"/editprofile\" class=\"profile-nav-item active\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>
                Редактировать
            </a>
        </nav>
    </aside>

    <div class=\"profile-main\">
        <div class=\"profile-card\">
            <h2 class=\"edit-title\">Редактирование профиля</h2>

            <div id=\"success-message\" class=\"edit-success\" style=\"display:none;\">
                Изменения сохранены!
            </div>

            <form id=\"edit-profile-form\">
                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"about\">Обо мне</label>
                    <textarea
                        id=\"about\"
                        name=\"about\"
                        class=\"edit-textarea\"
                        placeholder=\"Расскажите о себе...\"
                        rows=\"4\"
                    >{{ about }}</textarea>
                </div>

                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"interests\">Интересы</label>
                    <textarea
                        id=\"interests\"
                        name=\"interests\"
                        class=\"edit-textarea\"
                        placeholder=\"Ваши увлечения и интересы...\"
                        rows=\"4\"
                    >{{ interests }}</textarea>
                </div>

                <div class=\"edit-field\">
                    <label class=\"edit-label\" for=\"favorite_films\">Любимые фильмы</label>
                    <textarea
                        id=\"favorite_films\"
                        name=\"favorite_films\"
                        class=\"edit-textarea\"
                        placeholder=\"Список любимых фильмов...\"
                        rows=\"4\"
                    >{{ favorite_films }}</textarea>
                </div>

                <div class=\"edit-actions\">
                    <button type=\"submit\" class=\"btn-primary edit-save-btn\" id=\"save-btn\">
                        Сохранить
                    </button>
                    <a href=\"/id{{ user_id }}\" class=\"btn-secondary edit-cancel-btn\">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener(\x27DOMContentLoaded\x27, function() {
    const form = document.getElementById(\x27edit-profile-form\x27);
    const saveBtn = document.getElementById(\x27save-btn\x27);
    const successMsg = document.getElementById(\x27success-message\x27);

    form.addEventListener(\x27submit\x27, function(e) {
        e.preventDefault();

        const data = {
            about: document.getElementById(\x27about\x27).value,
            interests: document.getElementById(\x27interests\x27).value,
            favorite_films: document.getElementById(\x27favorite_films\x27).value
        };

        saveBtn.disabled = true;
        saveBtn.textContent = \x27Сохранение...\x27;

        fetch(\x27/api/profile/update\x27, {
            method: \x27POST\x27,
            headers: {
                \x27Content-Type\x27: \x27application/json\x27,
                \x27X-Requested-With\x27: \x27XMLHttpRequest\x27
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === \x27ok\x27) {
                successMsg.style.display = \x27block\x27;
                setTimeout(() => {
                    successMsg.style.display = \x27none\x27;
                }, 3000);
            } else {
                alert(\x27Ошибка сохранения\x27);
            }
        })
        .catch(() => {
            alert(\x27Ошибка сети\x27);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = \x27Сохранить\x27;
        });
    });
});
</script>
{% endblock %}
", "profile/edit.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\profile\\edit.twig");
    }
}
