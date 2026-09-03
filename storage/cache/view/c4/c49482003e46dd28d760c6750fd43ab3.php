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
        yield "    <div class=\"profile-main\">
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
        // line 21
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["about"]) || array_key_exists("about", $context) ? $context["about"] : (function () { throw new RuntimeError('Variable "about" does not exist.', 21, $this->source); })()), "html", null, true);
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
        // line 32
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["interests"]) || array_key_exists("interests", $context) ? $context["interests"] : (function () { throw new RuntimeError('Variable "interests" does not exist.', 32, $this->source); })()), "html", null, true);
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
        // line 43
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["favorite_films"]) || array_key_exists("favorite_films", $context) ? $context["favorite_films"] : (function () { throw new RuntimeError('Variable "favorite_films" does not exist.', 43, $this->source); })()), "html", null, true);
        yield "</textarea>
                </div>

                <div class=\"edit-actions\">
                    <button type=\"submit\" class=\"btn-primary edit-save-btn\" id=\"save-btn\">
                        Сохранить
                    </button>
                    <a href=\"/id";
        // line 50
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["user_id"]) || array_key_exists("user_id", $context) ? $context["user_id"] : (function () { throw new RuntimeError('Variable "user_id" does not exist.', 50, $this->source); })()), "html", null, true);
        yield "\" class=\"btn-secondary edit-cancel-btn\">
                        Отмена
                    </a>
                </div>
            </form>
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
        return array (  116 => 50,  106 => 43,  92 => 32,  78 => 21,  59 => 4,  52 => 3,  41 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{% extends \"layouts/main.twig\" %}

{% block content %}
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
{% endblock %}
", "profile/edit.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\profile\\edit.twig");
    }
}
