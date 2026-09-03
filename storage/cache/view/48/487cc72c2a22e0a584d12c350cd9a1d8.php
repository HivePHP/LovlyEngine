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

/* home/login.twig */
class __TwigTemplate_5313a288cfe4ccb5b16f6dd02243882c extends Template
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
        return "layouts/main_home.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("layouts/main_home.twig", 1);
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
        yield "
    <div class=\"info-card\">
        <h1>";
        // line 6
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 6, $this->source); })()), "name", [], "any", false, false, false, 6), "html", null, true);
        yield "</h1>

        <div class=\"slider\">
            <div class=\"slides\">
                <div class=\"slide active\">Зайди в свой аккаунт и будь на связи с друзьями, семьёй и всем миром.</div>
                <div class=\"slide\">Делись фотографиями, смотри ленту, участвуй в группах и находи новых друзей.</div>
                <div class=\"slide\">Будь в центре событий! Новости, общение, эмоции — всё здесь.</div>
                <div class=\"slide\">Создавай свой контент, собирай лайки, заводи новые знакомства!</div>
            </div>
        </div>

        <div class=\"visitors\">
            <span class=\"icon\"></span>
            <span class=\"count\">Онлайн сейчас: <b>";
        // line 19
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["users_count"]) || array_key_exists("users_count", $context) ? $context["users_count"] : (function () { throw new RuntimeError('Variable "users_count" does not exist.', 19, $this->source); })()), "html", null, true);
        yield "</b></span>
        </div>
    </div>
    <div class=\"login-card\">
        <h2>Авторизация</h2>

        <form id=\"login-form\">
            <div class=\"form-group\">
                <label>Email</label>
                <input
                        type=\"email\"
                        name=\"email\"
                        placeholder=\"Введите email\"
                        autocomplete=\"email\"
                />
            </div>

            <div class=\"form-group\">
                <label>Пароль</label>
                <input
                        type=\"password\"
                        name=\"password\"
                        placeholder=\"Введите пароль\"
                        autocomplete=\"current-password\"
                />
            </div>

            <div class=\"options\">
                <label class=\"ui-checkbox\">
                    <input type=\"checkbox\" name=\"remember\" value=\"1\">
                    <span class=\"ui-checkmark\"></span>
                    Запомнить меня
                </label>
            </div>

            <button class=\"btn-primary btn-primary-login\">
                Войти
            </button>
        </form>

        <div class=\"or-block\">
            <div class=\"or-line left\"></div>
            <span>ИЛИ</span>
            <div class=\"or-line right\"></div>
        </div>

        <div class=\"create-account\">
            <a href=\"/reg\" class=\"btn-secondary btn-secondary-login\">
                Создать аккаунт
            </a>
        </div>
    </div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "home/login.twig";
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
        return array (  79 => 19,  63 => 6,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/main_home.twig\" %}

{% block content %}

    <div class=\"info-card\">
        <h1>{{ app.name }}</h1>

        <div class=\"slider\">
            <div class=\"slides\">
                <div class=\"slide active\">Зайди в свой аккаунт и будь на связи с друзьями, семьёй и всем миром.</div>
                <div class=\"slide\">Делись фотографиями, смотри ленту, участвуй в группах и находи новых друзей.</div>
                <div class=\"slide\">Будь в центре событий! Новости, общение, эмоции — всё здесь.</div>
                <div class=\"slide\">Создавай свой контент, собирай лайки, заводи новые знакомства!</div>
            </div>
        </div>

        <div class=\"visitors\">
            <span class=\"icon\"></span>
            <span class=\"count\">Онлайн сейчас: <b>{{ users_count }}</b></span>
        </div>
    </div>
    <div class=\"login-card\">
        <h2>Авторизация</h2>

        <form id=\"login-form\">
            <div class=\"form-group\">
                <label>Email</label>
                <input
                        type=\"email\"
                        name=\"email\"
                        placeholder=\"Введите email\"
                        autocomplete=\"email\"
                />
            </div>

            <div class=\"form-group\">
                <label>Пароль</label>
                <input
                        type=\"password\"
                        name=\"password\"
                        placeholder=\"Введите пароль\"
                        autocomplete=\"current-password\"
                />
            </div>

            <div class=\"options\">
                <label class=\"ui-checkbox\">
                    <input type=\"checkbox\" name=\"remember\" value=\"1\">
                    <span class=\"ui-checkmark\"></span>
                    Запомнить меня
                </label>
            </div>

            <button class=\"btn-primary btn-primary-login\">
                Войти
            </button>
        </form>

        <div class=\"or-block\">
            <div class=\"or-line left\"></div>
            <span>ИЛИ</span>
            <div class=\"or-line right\"></div>
        </div>

        <div class=\"create-account\">
            <a href=\"/reg\" class=\"btn-secondary btn-secondary-login\">
                Создать аккаунт
            </a>
        </div>
    </div>
{% endblock %}", "home/login.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\home\\login.twig");
    }
}
