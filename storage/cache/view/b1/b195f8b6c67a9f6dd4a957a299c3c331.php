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
        yield "    <div class=\"profile-main\">

        <!-- VK-style profile header card: avatar + name + info -->
        <div class=\"profile-card profile-header-card\">
            <div class=\"profile-avatar profile-avatar-large\">
                <svg width=\"100\" height=\"100\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\">
                    <path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/>
                </svg>
            </div>

            <div class=\"profile-header-info\">
                <div class=\"profile-header-top\">
                    <h1 class=\"profile-name\">";
        // line 16
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 16, $this->source); })()), "html", null, true);
        yield " ";
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["surname"]) || array_key_exists("surname", $context) ? $context["surname"] : (function () { throw new RuntimeError('Variable "surname" does not exist.', 16, $this->source); })()), "html", null, true);
        yield "</h1>
                    <span class=\"profile-online\">Online</span>
                </div>

                <div class=\"profile-info\">
                    <div class=\"profile-info-row\">
                        <span class=\"profile-info-label\">Пол:</span>
                        <span class=\"profile-info-value\">";
        // line 23
        yield (string) ((((isset($context["sex"]) || array_key_exists("sex", $context) ? $context["sex"] : (function () { throw new RuntimeError('Variable "sex" does not exist.', 23, $this->source); })()) == "male")) ? ("Мужской") : (((((isset($context["sex"]) || array_key_exists("sex", $context) ? $context["sex"] : (function () { throw new RuntimeError('Variable "sex" does not exist.', 23, $this->source); })()) == "female")) ? ("Женский") : ("Другое"))));
        yield "</span>
                    </div>
                    <div class=\"profile-info-row\">
                        <span class=\"profile-info-label\">День рождения:</span>
                        <span class=\"profile-info-value\">";
        // line 27
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["birthday"]) || array_key_exists("birthday", $context) ? $context["birthday"] : (function () { throw new RuntimeError('Variable "birthday" does not exist.', 27, $this->source); })()), "html", null, true);
        yield "</span>
                    </div>
                    ";
        // line 29
        if ((($tmp = (isset($context["city"]) || array_key_exists("city", $context) ? $context["city"] : (function () { throw new RuntimeError('Variable "city" does not exist.', 29, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 30
            yield "                    <div class=\"profile-info-row\">
                        <span class=\"profile-info-label\">Город:</span>
                        <span class=\"profile-info-value\">";
            // line 32
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["city"]) || array_key_exists("city", $context) ? $context["city"] : (function () { throw new RuntimeError('Variable "city" does not exist.', 32, $this->source); })()), "html", null, true);
            yield "</span>
                    </div>
                    ";
        }
        // line 35
        yield "                    ";
        if ((($tmp = (isset($context["country"]) || array_key_exists("country", $context) ? $context["country"] : (function () { throw new RuntimeError('Variable "country" does not exist.', 35, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 36
            yield "                    <div class=\"profile-info-row\">
                        <span class=\"profile-info-label\">Страна:</span>
                        <span class=\"profile-info-value\">";
            // line 38
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["country"]) || array_key_exists("country", $context) ? $context["country"] : (function () { throw new RuntimeError('Variable "country" does not exist.', 38, $this->source); })()), "html", null, true);
            yield "</span>
                    </div>
                    ";
        }
        // line 41
        yield "                </div>

                ";
        // line 43
        if (((isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 43, $this->source); })()) == (isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 43, $this->source); })()))) {
            // line 44
            yield "                    <a href=\"/editprofile\" class=\"btn-secondary btn-sm header-edit-btn\">Редактировать профиль</a>
                ";
        }
        // line 46
        yield "            </div>
        </div>

        ";
        // line 49
        if ((((($tmp = (isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 49, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp) || (($tmp = (isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 49, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) || (($tmp = (isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 49, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp))) {
            // line 50
            yield "        <div class=\"profile-card\">
            <h3 class=\"profile-section-title\">Обо мне</h3>

            ";
            // line 53
            if ((($tmp = (isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 53, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 54
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Обо мне</div>
                <div class=\"profile-about-text\">";
                // line 56
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 56, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 59
            yield "
            ";
            // line 60
            if ((($tmp = (isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 60, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 61
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Интересы</div>
                <div class=\"profile-about-text\">";
                // line 63
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 63, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 66
            yield "
            ";
            // line 67
            if ((($tmp = (isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 67, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 68
                yield "            <div class=\"profile-about-section\">
                <div class=\"profile-about-label\">Любимые фильмы</div>
                <div class=\"profile-about-text\">";
                // line 70
                yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 70, $this->source); })()), "html", null, true);
                yield "</div>
            </div>
            ";
            }
            // line 73
            yield "        </div>
        ";
        }
        // line 75
        yield "
        ";
        // line 76
        if ((($tmp = (isset($context["userId"]) || array_key_exists("userId", $context) ? $context["userId"] : (function () { throw new RuntimeError('Variable "userId" does not exist.', 76, $this->source); })())) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 77
            yield "        <div class=\"profile-card\">
            <div class=\"profile-photo-upload\">
                <svg width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#1877f2\" stroke-width=\"2\"><rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"/><circle cx=\"8.5\" cy=\"8.5\" r=\"1.5\"/><polyline points=\"21 15 16 10 5 21\"/></svg>
                <span>Добавить фотографии</span>
            </div>
        </div>

        <div class=\"profile-card\">
            <div class=\"profile-wall-input\">
                <div class=\"profile-wall-avatar\">";
            // line 86
            yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["userInitials"]) || array_key_exists("userInitials", $context) ? $context["userInitials"] : (function () { throw new RuntimeError('Variable "userInitials" does not exist.', 86, $this->source); })()), "html", null, true);
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
        ";
        }
        // line 98
        yield "
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
        return array (  221 => 98,  206 => 86,  195 => 77,  193 => 76,  190 => 75,  186 => 73,  180 => 70,  176 => 68,  174 => 67,  171 => 66,  165 => 63,  161 => 61,  159 => 60,  156 => 59,  150 => 56,  146 => 54,  144 => 53,  139 => 50,  137 => 49,  132 => 46,  128 => 44,  126 => 43,  122 => 41,  116 => 38,  112 => 36,  109 => 35,  103 => 32,  99 => 30,  97 => 29,  92 => 27,  85 => 23,  73 => 16,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/main.twig\" %}

{% block content %}
    <div class=\"profile-main\">

        <!-- VK-style profile header card: avatar + name + info -->
        <div class=\"profile-card profile-header-card\">
            <div class=\"profile-avatar profile-avatar-large\">
                <svg width=\"100\" height=\"100\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"#b0b8c1\" stroke-width=\"1.2\">
                    <path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/>
                </svg>
            </div>

            <div class=\"profile-header-info\">
                <div class=\"profile-header-top\">
                    <h1 class=\"profile-name\">{{ name }} {{ surname }}</h1>
                    <span class=\"profile-online\">Online</span>
                </div>

                <div class=\"profile-info\">
                    <div class=\"profile-info-row\">
                        <span class=\"profile-info-label\">Пол:</span>
                        <span class=\"profile-info-value\">{{ sex == \x27male\x27 ? \x27Мужской\x27 : (sex == \x27female\x27 ? \x27Женский\x27 : \x27Другое\x27) }}</span>
                    </div>
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
                </div>

                {% if userId == user_id %}
                    <a href=\"/editprofile\" class=\"btn-secondary btn-sm header-edit-btn\">Редактировать профиль</a>
                {% endif %}
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

        {% if userId %}
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
        {% endif %}

    </div>
{% endblock %}
", "profile/profile.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\profile\\profile.twig");
    }
}
