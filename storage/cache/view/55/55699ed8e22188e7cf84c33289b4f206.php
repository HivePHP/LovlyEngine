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

/* layouts/main.twig */
class __TwigTemplate_d6c9c11ea86c846426060090d24e1cc3 extends Template
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

        $this->parent = false;

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!doctype html>
<html lang=\"ru\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    ";
        // line 7
        yield (string) $this->env->getFunction('assets_css')->getCallable()();
        yield "
    <title>";
        // line 8
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 8, $this->source); })()), "html", null, true);
        yield "</title>
</head>
<body>
    <header class=\"header\">
        <div class=\"header-inner\">
            <div class=\"header-left\">
                <a href=\"/\" class=\"header-logo\">";
        // line 14
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 14, $this->source); })()), "name", [], "any", false, false, false, 14), "html", null, true);
        yield "</a>
                <div class=\"header-search\">
                    <svg class=\"header-search-icon\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.35-4.35\"/></svg>
                    <input type=\"text\" placeholder=\"Поиск\" class=\"header-search-input\">
                </div>
            </div>

            <div class=\"header-right\">
                <button class=\"header-icon-btn\" title=\"Уведомления\">
                    <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\"/><path d=\"M13.73 21a2 2 0 0 1-3.46 0\"/></svg>
                </button>

                <div class=\"header-user\">
                    <div class=\"header-user-toggle\" id=\"userDropdownToggle\">
                        <div class=\"header-avatar\">";
        // line 28
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 28, $this->source); })()), "html", null, true);
        yield "</div>
                        <svg class=\"header-arrow\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"m6 9 6 6 6-6\"/></svg>
                    </div>
                    <div class=\"header-dropdown\" id=\"userDropdown\">
                        <div class=\"header-dropdown-header\">
                            <div class=\"header-dropdown-avatar\">";
        // line 33
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 33, $this->source); })()), "html", null, true);
        yield "</div>
                            <div class=\"header-dropdown-name\">";
        // line 34
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userName"]) || array_key_exists("userName", $context) ? $context["userName"] : (function () { throw new RuntimeError('Variable "userName" does not exist.', 34, $this->source); })()), "html", null, true);
        yield "</div>
                        </div>
                        <div class=\"header-dropdown-divider\"></div>
                        <a href=\"/id";
        // line 37
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 37, $this->source); })()), "html", null, true);
        yield "\" class=\"header-dropdown-item\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                            Моя страница
                        </a>
                        <a href=\"#\" class=\"header-dropdown-item\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42\"/></svg>
                            Настройки
                        </a>
                        <div class=\"header-dropdown-divider\"></div>
                        <a href=\"/logout\" class=\"header-dropdown-item header-dropdown-item--danger\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" y1=\"12\" x2=\"9\" y2=\"12\"/></svg>
                            Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class=\"container\">
        ";
        // line 57
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 58
        yield "    </main>

    <script>
    document.addEventListener(\x27DOMContentLoaded\x27, function() {
        const toggle = document.getElementById(\x27userDropdownToggle\x27);
        const dropdown = document.getElementById(\x27userDropdown\x27);
        if (toggle && dropdown) {
            toggle.addEventListener(\x27click\x27, function(e) {
                e.stopPropagation();
                dropdown.classList.toggle(\x27open\x27);
            });
            document.addEventListener(\x27click\x27, function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove(\x27open\x27);
                }
            });
        }
    });
    </script>
    ";
        // line 77
        yield (string) $this->env->getFunction('assets_js')->getCallable()();
        yield "
</body>
</html>
";
        yield from [];
    }

    // line 57
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "layouts/main.twig";
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
        return array (  155 => 57,  146 => 77,  125 => 58,  123 => 57,  100 => 37,  94 => 34,  90 => 33,  82 => 28,  65 => 14,  56 => 8,  52 => 7,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!doctype html>
<html lang=\"ru\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    {{ assets_css() }}
    <title>{{ title }}</title>
</head>
<body>
    <header class=\"header\">
        <div class=\"header-inner\">
            <div class=\"header-left\">
                <a href=\"/\" class=\"header-logo\">{{ app.name }}</a>
                <div class=\"header-search\">
                    <svg class=\"header-search-icon\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.35-4.35\"/></svg>
                    <input type=\"text\" placeholder=\"Поиск\" class=\"header-search-input\">
                </div>
            </div>

            <div class=\"header-right\">
                <button class=\"header-icon-btn\" title=\"Уведомления\">
                    <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\"/><path d=\"M13.73 21a2 2 0 0 1-3.46 0\"/></svg>
                </button>

                <div class=\"header-user\">
                    <div class=\"header-user-toggle\" id=\"userDropdownToggle\">
                        <div class=\"header-avatar\">{{ userInitials }}</div>
                        <svg class=\"header-arrow\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"m6 9 6 6 6-6\"/></svg>
                    </div>
                    <div class=\"header-dropdown\" id=\"userDropdown\">
                        <div class=\"header-dropdown-header\">
                            <div class=\"header-dropdown-avatar\">{{ userInitials }}</div>
                            <div class=\"header-dropdown-name\">{{ userName }}</div>
                        </div>
                        <div class=\"header-dropdown-divider\"></div>
                        <a href=\"/id{{ userId }}\" class=\"header-dropdown-item\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                            Моя страница
                        </a>
                        <a href=\"#\" class=\"header-dropdown-item\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42\"/></svg>
                            Настройки
                        </a>
                        <div class=\"header-dropdown-divider\"></div>
                        <a href=\"/logout\" class=\"header-dropdown-item header-dropdown-item--danger\">
                            <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" y1=\"12\" x2=\"9\" y2=\"12\"/></svg>
                            Выйти
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class=\"container\">
        {% block content %}{% endblock %}
    </main>

    <script>
    document.addEventListener(\x27DOMContentLoaded\x27, function() {
        const toggle = document.getElementById(\x27userDropdownToggle\x27);
        const dropdown = document.getElementById(\x27userDropdown\x27);
        if (toggle && dropdown) {
            toggle.addEventListener(\x27click\x27, function(e) {
                e.stopPropagation();
                dropdown.classList.toggle(\x27open\x27);
            });
            document.addEventListener(\x27click\x27, function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove(\x27open\x27);
                }
            });
        }
    });
    </script>
    {{ assets_js() }}
</body>
</html>
", "layouts/main.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\layouts\\main.twig");
    }
}
