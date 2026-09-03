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

/* profile/profile.twig */
class __TwigTemplate_90c20214f640d17dd9a234b15559069c extends Template
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
        yield "<div class=\"profile-layout\">

    <aside class=\"profile-sidebar\">
        <div class=\"profile-avatar-card\">
            <div class=\"profile-avatar\">
                <svg width=\"80\" height=\"80\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
            </div>
            ";
        // line 11
        if (((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 11, $this->source); })()) == (isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 11, $this->source); })()))) {
            // line 12
            yield "            <a href=\"/editprofile\" class=\"btn-secondary btn-sm\">Редактировать</a>
            ";
        }
        // line 14
        yield "        </div>

        <nav class=\"profile-nav\">
            <a href=\"/id";
        // line 17
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 17, $this->source); })()), "html", null, true);
        yield "\" class=\"profile-nav-item active\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                Моя страница
            </a>
            ";
        // line 21
        if (((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 21, $this->source); })()) == (isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 21, $this->source); })()))) {
            // line 22
            yield "            <a href=\"/editprofile\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>
                Редактировать
            </a>
            ";
        }
        // line 27
        yield "            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>
                Друзья
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z\"/></svg>
                Сообщения
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
                Фотографии
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M8 14s1.5 2 4 2 4-2 4-2\"/><line x1=\"9\" y1=\"9\" x2=\"9.01\" y2=\"9\"/><line x1=\"15\" y1=\"9\" x2=\"15.01\" y2=\"9\"/></svg>
                Группы
            </a>
        </nav>
    </aside>

    <div class=\"profile-main\">

        <div class=\"profile-card\">
            <div class=\"profile-card-header\">
                <h1 class=\"profile-name\">";
        // line 50
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 50, $this->source); })()), "html", null, true);
        yield " ";
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["surname"]) || array_key_exists("surname", $context) ? $context["surname"] : (function () { throw new RuntimeError('Variable "surname" does not exist.', 50, $this->source); })()), "html", null, true);
        yield "</h1>
                <span class=\"profile-online\">Online</span>
            </div>

            <div class=\"profile-status\">
                <input type=\"text\" class=\"profile-status-input\" placeholder=\"Изменить статус\" disabled>
            </div>

            <div class=\"profile-info\">
                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">День рождения:</span>
                    <span class=\"profile-info-value\">";
        // line 61
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["birthday"]) || array_key_exists("birthday", $context) ? $context["birthday"] : (function () { throw new RuntimeError('Variable "birthday" does not exist.', 61, $this->source); })()), "html", null, true);
        yield "</span>
                </div>
                ";
        // line 63
        if ((($tmp = (isset($context["city"]) || array_key_exists("city", $context) ? $context["city"] : (function () { throw new RuntimeError('Variable "city" does not exist.', 63, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 64
            yield "                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Город:</span>
                    <span class=\"profile-info-value\">";
            // line 66
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["city"]) || array_key_exists("city", $context) ? $context["city"] : (function () { throw new RuntimeError('Variable "city" does not exist.', 66, $this->source); })()), "html", null, true);
            yield "</span>
                </div>
                ";
        }
        // line 69
        yield "                ";
        if ((($tmp = (isset($context["country"]) || array_key_exists("country", $context) ? $context["country"] : (function () { throw new RuntimeError('Variable "country" does not exist.', 69, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 70
            yield "                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Страна:</span>
                    <span class=\"profile-info-value\">";
            // line 72
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["country"]) || array_key_exists("country", $context) ? $context["country"] : (function () { throw new RuntimeError('Variable "country" does not exist.', 72, $this->source); })()), "html", null, true);
            yield "</span>
                </div>
                ";
        }
        // line 75
        yield "                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Пол:</span>
                    <span class=\"profile-info-value\">";
        // line 77
        yield (string) ((((isset($context["sex"]) || array_key_exists("sex", $context) ? $context["sex"] : (function () { throw new RuntimeError('Variable "sex" does not exist.', 77, $this->source); })()) == "male")) ? ("Мужской") : (((((isset($context["sex"]) || array_key_exists("sex", $context) ? $context["sex"] : (function () { throw new RuntimeError('Variable "sex" does not exist.', 77, $this->source); })()) == "female")) ? ("Женский") : ("Другое"))));
        yield "</span>
                </div>
            </div>
        </div>

        ";
        // line 82
        if ((((($tmp = (isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 82, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp) || (($tmp = (isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 82, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) || (($tmp = (isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 82, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp))) {
            // line 83
            yield "        <div class=\"profile-card\">
            <h3 class=\"profile-section-title\">Обо мне</h3>

            ";
            // line 86
            if ((($tmp = (isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 86, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 87
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Обо мне</div>
                <div class=\"profile-about-text\">";
                // line 89
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 89, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 92
            yield "
            ";
            // line 93
            if ((($tmp = (isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 93, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 94
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Интересы</div>
                <div class=\"profile-about-text\">";
                // line 96
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 96, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 99
            yield "
            ";
            // line 100
            if ((($tmp = (isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 100, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 101
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Любимые фильмы</div>
                <div class=\"profile-about-text\">";
                // line 103
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 103, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 106
            yield "        </div>
        ";
        }
        // line 108
        yield "
        <div class=\"profile-card\">
            <div class=\"profile-photo-upload\">
                <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#1877f2\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
                <span>Добавить фотографии</span>
            </div>
        </div>

        <div class=\"profile-card\">
            <div class=\"profile-wall-input\">
                <div class=\"profile-wall-avatar\">";
        // line 118
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 118, $this->source); })()), "html", null, true);
        yield "</div>
                <div class=\"profile-wall-placeholder\">Что у Вас нового?</div>
            </div>
        </div>

        <div class=\"profile-card profile-wall-empty\">
            <div class=\"profile-wall-empty-icon\">
                <svg width=\"64\" height=\"64\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
            </div>
            <p class=\"profile-wall-empty-text\">На стене пока ни одной записи.</p>
        </div>

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
        return "profile/profile.twig";
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
        return array (  243 => 118,  231 => 108,  227 => 106,  221 => 103,  217 => 101,  215 => 100,  212 => 99,  206 => 96,  202 => 94,  200 => 93,  197 => 92,  191 => 89,  187 => 87,  185 => 86,  180 => 83,  178 => 82,  170 => 77,  166 => 75,  160 => 72,  156 => 70,  153 => 69,  147 => 66,  143 => 64,  141 => 63,  136 => 61,  120 => 50,  95 => 27,  88 => 22,  86 => 21,  79 => 17,  74 => 14,  70 => 12,  68 => 11,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/main.twig\" %}

{% block content %}
<div class=\"profile-layout\">

    <aside class=\"profile-sidebar\">
        <div class=\"profile-avatar-card\">
            <div class=\"profile-avatar\">
                <svg width=\"80\" height=\"80\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
            </div>
            {% if userId == user_id %}
            <a href=\"/editprofile\" class=\"btn-secondary btn-sm\">Редактировать</a>
            {% endif %}
        </div>

        <nav class=\"profile-nav\">
            <a href=\"/id{{ user_id }}\" class=\"profile-nav-item active\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/></svg>
                Моя страница
            </a>
            {% if userId == user_id %}
            <a href=\"/editprofile\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z\"/></svg>
                Редактировать
            </a>
            {% endif %}
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"/><circle cx=\"9\" cy=\"7\" r=\"4\"/><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"/><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"/></svg>
                Друзья
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><path d=\"M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z\"/></svg>
                Сообщения
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
                Фотографии
            </a>
            <a href=\"#\" class=\"profile-nav-item\">
                <svg width=\"18\" height=\"18\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M8 14s1.5 2 4 2 4-2 4-2\"/><line x1=\"9\" y1=\"9\" x2=\"9.01\" y2=\"9\"/><line x1=\"15\" y1=\"9\" x2=\"15.01\" y2=\"9\"/></svg>
                Группы
            </a>
        </nav>
    </aside>

    <div class=\"profile-main\">

        <div class=\"profile-card\">
            <div class=\"profile-card-header\">
                <h1 class=\"profile-name\">{{ name }} {{ surname }}</h1>
                <span class=\"profile-online\">Online</span>
            </div>

            <div class=\"profile-status\">
                <input type=\"text\" class=\"profile-status-input\" placeholder=\"Изменить статус\" disabled>
            </div>

            <div class=\"profile-info\">
                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">День рождения:</span>
                    <span class=\"profile-info-value\">{{ birthday }}</span>
                </div>
                {% if city %}
                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Город:</span>
                    <span class=\"profile-info-value\">{{ city }}</span>
                </div>
                {% endif %}
                {% if country %}
                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Страна:</span>
                    <span class=\"profile-info-value\">{{ country }}</span>
                </div>
                {% endif %}
                <div class=\"profile-info-row\">
                    <span class=\"profile-info-label\">Пол:</span>
                    <span class=\"profile-info-value\">{{ sex == \x27male\x27 ? \x27Мужской\x27 : (sex == \x27female\x27 ? \x27Женский\x27 : \x27Другое\x27) }}</span>
                </div>
            </div>
        </div>

        {% if about or interests or favorite_films %}
        <div class=\"profile-card\">
            <h3 class=\"profile-section-title\">Обо мне</h3>

            {% if about %}
            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Обо мне</div>
                <div class=\"profile-about-text\">{{ about }}</div>
            </div>
            {% endif %}

            {% if interests %}
            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Интересы</div>
                <div class=\"profile-about-text\">{{ interests }}</div>
            </div>
            {% endif %}

            {% if favorite_films %}
            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Любимые фильмы</div>
                <div class=\"profile-about-text\">{{ favorite_films }}</div>
            </div>
            {% endif %}
        </div>
        {% endif %}

        <div class=\"profile-card\">
            <div class=\"profile-photo-upload\">
                <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#1877f2\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
                <span>Добавить фотографии</span>
            </div>
        </div>

        <div class=\"profile-card\">
            <div class=\"profile-wall-input\">
                <div class=\"profile-wall-avatar\">{{ userInitials }}</div>
                <div class=\"profile-wall-placeholder\">Что у Вас нового?</div>
            </div>
        </div>

        <div class=\"profile-card profile-wall-empty\">
            <div class=\"profile-wall-empty-icon\">
                <svg width=\"64\" height=\"64\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
            </div>
            <p class=\"profile-wall-empty-text\">На стене пока ни одной записи.</p>
        </div>

    </div>
</div>
{% endblock %}
", "profile/profile.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\profile\\profile.twig");
    }
}
