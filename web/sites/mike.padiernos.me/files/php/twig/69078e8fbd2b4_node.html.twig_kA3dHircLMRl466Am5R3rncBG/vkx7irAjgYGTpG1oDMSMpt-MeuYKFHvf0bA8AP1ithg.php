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

/* themes/custom/minim/source/04-symbiosis/node/node.html.twig */
class __TwigTemplate_4721063e7a2da087ea95993131c481b5 extends Template
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
            'node_header' => [$this, 'block_node_header'],
            'node_content' => [$this, 'block_node_content'],
            'node_footer' => [$this, 'block_node_footer'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 12
        yield "
";
        // line 14
        $context["classes"] = ["node", \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 16
($context["node"] ?? null), "bundle", [], "any", false, false, true, 16)), (((($tmp =         // line 17
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (\Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null))) : ("")), "grid"];
        // line 21
        yield "
<article";
        // line 22
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 22), "html", null, true);
        yield ">

  ";
        // line 24
        yield from $this->unwrap()->yieldBlock('node_header', $context, $blocks);
        // line 27
        yield "
  ";
        // line 28
        yield from $this->unwrap()->yieldBlock('node_content', $context, $blocks);
        // line 31
        yield "
  ";
        // line 32
        yield from $this->unwrap()->yieldBlock('node_footer', $context, $blocks);
        // line 35
        yield "
</article>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes"]);        yield from [];
    }

    // line 24
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_header(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 25
        yield "    ";
        yield from $this->load("@organisms/node-header/node-header.html.twig", 25)->unwrap()->yield($context);
        // line 26
        yield "  ";
        yield from [];
    }

    // line 28
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 29
        yield "    ";
        yield from $this->load("@organisms/node-content/node-content.html.twig", 29)->unwrap()->yield($context);
        // line 30
        yield "  ";
        yield from [];
    }

    // line 32
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_footer(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 33
        yield "    ";
        yield from $this->load("@organisms/node-footer/node-footer.html.twig", 33)->unwrap()->yield($context);
        // line 34
        yield "  ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/minim/source/04-symbiosis/node/node.html.twig";
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
        return array (  123 => 34,  120 => 33,  113 => 32,  108 => 30,  105 => 29,  98 => 28,  93 => 26,  90 => 25,  83 => 24,  74 => 35,  72 => 32,  69 => 31,  67 => 28,  64 => 27,  62 => 24,  57 => 22,  54 => 21,  52 => 17,  51 => 16,  50 => 14,  47 => 12,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/minim/source/04-symbiosis/node/node.html.twig", "/home/padiernos/public_html/web/themes/custom/minim/source/04-symbiosis/node/node.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 14, "block" => 24, "include" => 25];
        static $filters = ["clean_class" => 16, "escape" => 22];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'include'],
                ['clean_class', 'escape'],
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
