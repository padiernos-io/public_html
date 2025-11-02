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

/* @symbiosis/node/node--blog.html.twig */
class __TwigTemplate_91868f26ffb004e77f43f954991f76bb extends Template
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
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (\Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null))) : ("")), "blog", "grid"];
        // line 22
        yield "
<article";
        // line 23
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 23), "html", null, true);
        yield ">

  ";
        // line 25
        yield from $this->unwrap()->yieldBlock('node_header', $context, $blocks);
        // line 28
        yield "
  ";
        // line 29
        yield from $this->unwrap()->yieldBlock('node_content', $context, $blocks);
        // line 32
        yield "
  ";
        // line 33
        yield from $this->unwrap()->yieldBlock('node_footer', $context, $blocks);
        // line 36
        yield "
</article>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes"]);        yield from [];
    }

    // line 25
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_header(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 26
        yield "    ";
        yield from $this->load("@organisms/node-header/node-header__blog.html.twig", 26)->unwrap()->yield($context);
        // line 27
        yield "  ";
        yield from [];
    }

    // line 29
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 30
        yield "    ";
        yield from $this->load("@organisms/node-content/node-content__blog.html.twig", 30)->unwrap()->yield($context);
        // line 31
        yield "  ";
        yield from [];
    }

    // line 33
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_node_footer(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 34
        yield "    ";
        yield from $this->load("@organisms/node-footer/node-footer__blog.html.twig", 34)->unwrap()->yield($context);
        // line 35
        yield "  ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@symbiosis/node/node--blog.html.twig";
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
        return array (  123 => 35,  120 => 34,  113 => 33,  108 => 31,  105 => 30,  98 => 29,  93 => 27,  90 => 26,  83 => 25,  74 => 36,  72 => 33,  69 => 32,  67 => 29,  64 => 28,  62 => 25,  57 => 23,  54 => 22,  52 => 17,  51 => 16,  50 => 14,  47 => 12,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@symbiosis/node/node--blog.html.twig", "themes/custom/minim/source/04-symbiosis/node/node--blog.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 14, "block" => 25, "include" => 26];
        static $filters = ["clean_class" => 16, "escape" => 23];
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
