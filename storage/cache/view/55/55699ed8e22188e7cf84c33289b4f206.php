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
    <meta name=\"csrf-token\" content=\"";
        // line 7
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["csrfToken"]) || array_key_exists("csrfToken", $context) ? $context["csrfToken"] : (function () { throw new RuntimeError('Variable "csrfToken" does not exist.', 7, $this->source); })()), "html", null, true);
        yield "\">
    ";
        // line 8
        yield (string) $this->env->getFunction('assets_css')->getCallable()();
        yield "
    <title>";
        // line 9
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 9, $this->source); })()), "html", null, true);
        yield "</title>
</head>
<body>
    <header class=\"header\">
        <div class=\"header-inner\">
            <div class=\"header-left\">
                <a href=\"/\" class=\"header-logo\">";
        // line 15
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 15, $this->source); })()), "name", [], "any", false, false, false, 15), "html", null, true);
        yield "</a>
                <div class=\"header-search\">
                    <svg class=\"header-search-icon\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"11\" cy=\"11\" r=\"8\"/><path d=\"m21 21-4.35-4.35\"/></svg>
                    <input type=\"text\" placeholder=\"Поиск\" class=\"header-search-input\">
                </div>
            </div>

            <div class=\"header-right\">
                ";
        // line 23
        if ((($tmp = (isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 23, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 24
            yield "                    <button class=\"header-icon-btn\" title=\"Уведомления\">
                        <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9\"/><path d=\"M13.73 21a2 2 0 0 1-3.46 0\"/></svg>
                    </button>

                    <div class=\"header-user\">
                        <div class=\"header-user-toggle\" id=\"userDropdownToggle\">
                            <div class=\"header-avatar\">";
            // line 30
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 30, $this->source); })()), "html", null, true);
            yield "</div>
                            <svg class=\"header-arrow\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"m6 9 6 6 6-6\"/></svg>
                        </div>
                        <div class=\"header-dropdown\" id=\"userDropdown\">
                            <div class=\"header-dropdown-header\">
                                <div class=\"header-dropdown-avatar\">";
            // line 35
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 35, $this->source); })()), "html", null, true);
            yield "</div>
                                <div class=\"header-dropdown-name\">";
            // line 36
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userName"]) || array_key_exists("userName", $context) ? $context["userName"] : (function () { throw new RuntimeError('Variable "userName" does not exist.', 36, $this->source); })()), "html", null, true);
            yield "</div>
                            </div>
                            <div class=\"header-dropdown-divider\"></div>
                            <a href=\"/id";
            // line 39
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 39, $this->source); })()), "html", null, true);
            yield "\" class=\"header-dropdown-item\">
                                <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                                Моя страница
                            </a>
                            <a href=\"#\" class=\"header-dropdown-item\">
                                <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42\"/></svg>
                                Настройки
                            </a>
                            <div class=\"header-dropdown-divider\"></div>
                            <form method=\"post\" action=\"/logout\" class=\"header-dropdown-form\" data-confirm=\"Выйти из аккаунта?\">
                                <input type=\"hidden\" name=\"csrf_token\" value=\"";
            // line 49
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["csrfToken"]) || array_key_exists("csrfToken", $context) ? $context["csrfToken"] : (function () { throw new RuntimeError('Variable "csrfToken" does not exist.', 49, $this->source); })()), "html", null, true);
            yield "\">
                                <button type=\"submit\" class=\"header-dropdown-item header-dropdown-item--danger header-dropdown-btn\">
                                    <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" y1=\"12\" x2=\"9\" y2=\"12\"/></svg>
                                    Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                ";
        } else {
            // line 58
            yield "                    <div class=\"header-auth-buttons\">
                        <a href=\"/\" class=\"header-login-btn\">Войти</a>
                        <a href=\"/reg\" class=\"header-register-btn\">Регистрация</a>
                    </div>
                ";
        }
        // line 63
        yield "            </div>
        </div>
    </header>

    <main class=\"container page-container\">
        <div class=\"page-layout\">
            <aside class=\"page-sidebar\">
                ";
        // line 70
        if ((($tmp = (isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 70, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 71
            yield "                    <nav class=\"profile-nav\">
                        <a href=\"/id";
            // line 72
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 72, $this->source); })()), "html", null, true);
            yield "\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>Моя страница</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>Друзья</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z\"/></svg>Сообщения</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>Фотографии</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M8 14s1.5 2 4 2 4-2 4-2\"/><line x1=\"9\" y1=\"9\" x2=\"9.01\" y2=\"9\"/><line x1=\"15\" y1=\"9\" x2=\"15.01\" y2=\"9\"/></svg>Группы</a>
                    </nav>
                ";
        } else {
            // line 79
            yield "                    <div class=\"login-card-compact\">
                        <h3 class=\"login-compact-title\">Вход</h3>
                        <form id=\"login-form-compact\">
                            <input type=\"hidden\" name=\"csrf_token\" value=\"";
            // line 82
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["csrfToken"]) || array_key_exists("csrfToken", $context) ? $context["csrfToken"] : (function () { throw new RuntimeError('Variable "csrfToken" does not exist.', 82, $this->source); })()), "html", null, true);
            yield "\">
                            <div class=\"form-group\">
                                <input type=\"email\" name=\"email\" placeholder=\"Email\" autocomplete=\"email\" required />
                            </div>
                            <div class=\"form-group\">
                                <input type=\"password\" name=\"password\" placeholder=\"Пароль\" autocomplete=\"current-password\" required />
                            </div>
                            <button type=\"submit\" class=\"btn-primary btn-primary-login\">Войти</button>
                        </form>
                        <div class=\"login-compact-footer\">
                            <a href=\"/reg\">Регистрация</a>
                        </div>
                    </div>
                ";
        }
        // line 96
        yield "            </aside>

            <div class=\"page-content\">
                ";
        // line 99
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 100
        yield "            </div>
        </div>
    </main>

    ";
        // line 104
        yield (string) $this->env->getFunction('assets_js')->getCallable()();
        yield "
</body>
</html>
";
        yield from [];
    }

    // line 99
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
        return array (  208 => 99,  199 => 104,  193 => 100,  191 => 99,  186 => 96,  169 => 82,  164 => 79,  154 => 72,  151 => 71,  149 => 70,  140 => 63,  133 => 58,  121 => 49,  108 => 39,  102 => 36,  98 => 35,  90 => 30,  82 => 24,  80 => 23,  69 => 15,  60 => 9,  56 => 8,  52 => 7,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!doctype html>
<html lang=\"ru\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    <meta name=\"csrf-token\" content=\"{{ csrfToken }}\">
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
                {% if userId %}
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
                            <form method=\"post\" action=\"/logout\" class=\"header-dropdown-form\" data-confirm=\"Выйти из аккаунта?\">
                                <input type=\"hidden\" name=\"csrf_token\" value=\"{{ csrfToken }}\">
                                <button type=\"submit\" class=\"header-dropdown-item header-dropdown-item--danger header-dropdown-btn\">
                                    <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" y1=\"12\" x2=\"9\" y2=\"12\"/></svg>
                                    Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                {% else %}
                    <div class=\"header-auth-buttons\">
                        <a href=\"/\" class=\"header-login-btn\">Войти</a>
                        <a href=\"/reg\" class=\"header-register-btn\">Регистрация</a>
                    </div>
                {% endif %}
            </div>
        </div>
    </header>

    <main class=\"container page-container\">
        <div class=\"page-layout\">
            <aside class=\"page-sidebar\">
                {% if userId %}
                    <nav class=\"profile-nav\">
                        <a href=\"/id{{ userId }}\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>Моя страница</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>Друзья</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z\"/></svg>Сообщения</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>Фотографии</a>
                        <a href=\"#\" class=\"profile-nav-item\"><svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M8 14s1.5 2 4 2 4-2 4-2\"/><line x1=\"9\" y1=\"9\" x2=\"9.01\" y2=\"9\"/><line x1=\"15\" y1=\"9\" x2=\"15.01\" y2=\"9\"/></svg>Группы</a>
                    </nav>
                {% else %}
                    <div class=\"login-card-compact\">
                        <h3 class=\"login-compact-title\">Вход</h3>
                        <form id=\"login-form-compact\">
                            <input type=\"hidden\" name=\"csrf_token\" value=\"{{ csrfToken }}\">
                            <div class=\"form-group\">
                                <input type=\"email\" name=\"email\" placeholder=\"Email\" autocomplete=\"email\" required />
                            </div>
                            <div class=\"form-group\">
                                <input type=\"password\" name=\"password\" placeholder=\"Пароль\" autocomplete=\"current-password\" required />
                            </div>
                            <button type=\"submit\" class=\"btn-primary btn-primary-login\">Войти</button>
                        </form>
                        <div class=\"login-compact-footer\">
                            <a href=\"/reg\">Регистрация</a>
                        </div>
                    </div>
                {% endif %}
            </aside>

            <div class=\"page-content\">
                {% block content %}{% endblock %}
            </div>
        </div>
    </main>

    {{ assets_js() }}
</body>
</html>
", "layouts/main.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\layouts\\main.twig");
    }
}
