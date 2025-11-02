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

/* themes/custom/minim/source/03-organisms/block/block--system-branding-block.html.twig */
class __TwigTemplate_4935b9077a0b1b3c400dba6be4c8f3b6 extends Template
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
            'block' => [$this, 'block_block'],
            'block_content' => [$this, 'block_block_content'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 1
        return "@organisms/block/block.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("@organisms/block/block.html.twig", 1);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["configuration", "plugin_id", "attributes", "site_logo", "site_name", "site_slogan"]);    }

    // line 17
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 18
        yield "  ";
        $context["classes"] = ["block", ("block-" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 20
($context["configuration"] ?? null), "provider", [], "any", false, false, true, 20))), ("block-" . \Drupal\Component\Utility\Html::getClass(Twig\Extension\CoreExtension::replace(        // line 21
($context["plugin_id"] ?? null), [":" => "--"]))), "flex", "site-branding"];
        // line 25
        yield "
  <div";
        // line 26
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 26), "html", null, true);
        yield ">

    ";
        // line 28
        yield from $this->unwrap()->yieldBlock('block_content', $context, $blocks);
        // line 43
        yield "
  </div>

";
        yield from [];
    }

    // line 28
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 29
        yield "
      ";
        // line 30
        if ((($tmp = ($context["site_logo"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 31
            yield "        ";
            yield from $this->load("@molecules/site_logo/site-logo.html.twig", 31)->unwrap()->yield($context);
            // line 32
            yield "      ";
        }
        // line 33
        yield "
      ";
        // line 34
        if ((($tmp = ($context["site_name"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 35
            yield "        ";
            yield from $this->load("@molecules/site_name/site-name.html.twig", 35)->unwrap()->yield($context);
            // line 36
            yield "      ";
        }
        // line 37
        yield "
      ";
        // line 38
        if ((($tmp = ($context["site_slogan"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 39
            yield "        ";
            yield from $this->load("@molecules/site_slogan/site-slogan.html.twig", 39)->unwrap()->yield($context);
            // line 40
            yield "      ";
        }
        // line 41
        yield "
    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/minim/source/03-organisms/block/block--system-branding-block.html.twig";
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
        return array (  125 => 41,  122 => 40,  119 => 39,  117 => 38,  114 => 37,  111 => 36,  108 => 35,  106 => 34,  103 => 33,  100 => 32,  97 => 31,  95 => 30,  92 => 29,  85 => 28,  77 => 43,  75 => 28,  70 => 26,  67 => 25,  65 => 21,  64 => 20,  62 => 18,  55 => 17,  43 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/minim/source/03-organisms/block/block--system-branding-block.html.twig", "/home/padiernos/public_html/web/themes/custom/minim/source/03-organisms/block/block--system-branding-block.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["extends" => 1, "set" => 18, "block" => 28, "if" => 30, "include" => 31];
        static $filters = ["clean_class" => 20, "replace" => 21, "escape" => 26];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['extends', 'set', 'block', 'if', 'include'],
                ['clean_class', 'replace', 'escape'],
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
