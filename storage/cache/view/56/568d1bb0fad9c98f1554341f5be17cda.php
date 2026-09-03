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
    <meta name=\"csrf-token\" content=\"";
        // line 8
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["csrfToken"]) || array_key_exists("csrfToken", $context) ? $context["csrfToken"] : (function () { throw new RuntimeError('Variable "csrfToken" does not exist.', 8, $this->source); })()), "html", null, true);
        yield "\">
    ";
        // line 9
        yield (string) $this->env->getFunction('assets_css')->getCallable()();
        yield "
    <title>";
        // line 10
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 10, $this->source); })()), "html", null, true);
        yield " - ";
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 10, $this->source); })()), "name", [], "any", false, false, false, 10), "html", null, true);
        yield "</title>
</head>
<body class=\"body-home\">

    <div class=\"container\">
        ";
        // line 15
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 16
        yield "    </div>

    <div class=\"footer-home\">
        <div class=\"container\">
            <div class=\"copy-footer-home\">
                &copy; 2025 ";
        // line 21
        yield (string) $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 21, $this->source); })()), "name", [], "any", false, false, false, 21), "html", null, true);
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
        // line 32
        yield (string) $this->env->getFunction('assets_js')->getCallable()();
        yield "
</body>
</html>";
        yield from [];
    }

    // line 15
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
        return array (  102 => 15,  94 => 32,  80 => 21,  73 => 16,  71 => 15,  61 => 10,  57 => 9,  53 => 8,  44 => 1,);
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
    <meta name=\"csrf-token\" content=\"{{ csrfToken }}\">
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
