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

/* layouts/main_home.twig */
class __TwigTemplate_b077f0855ba6586af0aee04ff9320e4c extends Template
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
    <meta name=\"viewport\"
           content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    ";
        // line 8
        yield (string) $this->env->getFunction('assets_css')->getCallable()();
        yield "
    <title>";
        // line 9
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 9, $this->source); })()), "html", null, true);
        yield " - ";
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 9, $this->source); })()), "name", [], "any", false, false, false, 9), "html", null, true);
        yield "</title>
</head>
<body class=\"body-home\">

    <div class=\"container\">
        ";
        // line 14
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 15
        yield "    </div>

    <div class=\"footer-home\">
        <div class=\"container\">
            <div class=\"copy-footer-home\">
                &copy; 2025 ";
        // line 20
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 20, $this->source); })()), "name", [], "any", false, false, false, 20), "html", null, true);
        yield ". Все права защищены.
            </div>
            <div class=\"button-footer-home\">
                <a href=\"\">О проекте</a>
                <a href=\"\">Блог</a>
                <a href=\"\">Правила пользования</a>
                <a href=\"\">Разработчикам</a>
            </div>
        </div>
    </div>

    ";
        // line 31
        yield (string) $this->env->getFunction('assets_js')->getCallable()();
        yield "
</body>
</html>";
        yield from [];
    }

    // line 14
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
        return "layouts/main_home.twig";
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
        return array (  98 => 14,  90 => 31,  76 => 20,  69 => 15,  67 => 14,  57 => 9,  53 => 8,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!doctype html>
<html lang=\"ru\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\"
           content=\"width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"ie=edge\">
    {{ assets_css() }}
    <title>{{ title }} - {{ app.name }}</title>
</head>
<body class=\"body-home\">

    <div class=\"container\">
        {% block content %}{% endblock %}
    </div>

    <div class=\"footer-home\">
        <div class=\"container\">
            <div class=\"copy-footer-home\">
                &copy; 2025 {{ app.name }}. Все права защищены.
            </div>
            <div class=\"button-footer-home\">
                <a href=\"\">О проекте</a>
                <a href=\"\">Блог</a>
                <a href=\"\">Правила пользования</a>
                <a href=\"\">Разработчикам</a>
            </div>
        </div>
    </div>

    {{ assets_js() }}
</body>
</html>", "layouts/main_home.twig", "E:\\OSPanel\\home\\hivephp.local\\resources\\views\\layouts\\main_home.twig");
    }
}
