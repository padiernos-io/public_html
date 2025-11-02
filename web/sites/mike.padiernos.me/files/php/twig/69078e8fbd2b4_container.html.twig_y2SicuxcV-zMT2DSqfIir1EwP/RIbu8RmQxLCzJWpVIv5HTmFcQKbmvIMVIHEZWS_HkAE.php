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
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* themes/custom/minim/source/03-organisms/container/container.html.twig */
class __TwigTemplate_c3e604b6c603edcfb03d54704a1c39c4 extends Template
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
            'container' => [$this, 'block_container'],
            'container_children' => [$this, 'block_container_children'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 25
        $context["classes"] = [(((($tmp =         // line 26
($context["has_parent"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("js-form-wrapper") : ("")), (((($tmp =         // line 27
($context["has_parent"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("form-wrapper") : (""))];
        // line 30
        yield "
";
        // line 31
        yield from $this->unwrap()->yieldBlock('container', $context, $blocks);
        // line 40
        yield "
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["has_parent", "attributes", "children"]);        yield from [];
    }

    // line 31
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_container(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 32
        yield "
  <div";
        // line 33
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 33), "html", null, true);
        yield ">
    ";
        // line 34
        yield from $this->unwrap()->yieldBlock('container_children', $context, $blocks);
        // line 37
        yield "  </div>

";
        yield from [];
    }

    // line 34
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_container_children(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 35
        yield "      ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["children"] ?? null), "html", null, true);
        yield "
    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/minim/source/03-organisms/container/container.html.twig";
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
        return array (  92 => 35,  85 => 34,  78 => 37,  76 => 34,  72 => 33,  69 => 32,  62 => 31,  55 => 40,  53 => 31,  50 => 30,  48 => 27,  47 => 26,  46 => 25,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/minim/source/03-organisms/container/container.html.twig", "/home/padiernos/public_html/web/themes/custom/minim/source/03-organisms/container/container.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 25, "block" => 31];
        static $filters = ["escape" => 33];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block'],
                ['escape'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
