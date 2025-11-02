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

/* @organisms/block/block.html.twig */
class __TwigTemplate_916bab60c68c3fe50279714f503b5480 extends Template
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
            'block' => [$this, 'block_block'],
            'block_content' => [$this, 'block_block_content'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 30
        $context["classes"] = ["block", ("block-" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 32
($context["configuration"] ?? null), "provider", [], "any", false, false, true, 32))), ("block-" . \Drupal\Component\Utility\Html::getClass(Twig\Extension\CoreExtension::replace(        // line 33
($context["plugin_id"] ?? null), [":" => "--"]))), "grid"];
        // line 36
        yield "
";
        // line 37
        yield from $this->unwrap()->yieldBlock('block', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["configuration", "plugin_id", "attributes", "content"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_block(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 38
        yield "
  <div";
        // line 39
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 39), "html", null, true);
        yield ">
    ";
        // line 40
        yield from $this->load("@molecules/title/title--label.html.twig", 40)->unwrap()->yield($context);
        // line 41
        yield "    ";
        yield from $this->unwrap()->yieldBlock('block_content', $context, $blocks);
        // line 44
        yield "  </div>

";
        yield from [];
    }

    // line 41
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 42
        yield "      ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true);
        yield "
    ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@organisms/block/block.html.twig";
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
        return array (  91 => 42,  84 => 41,  77 => 44,  74 => 41,  72 => 40,  68 => 39,  65 => 38,  53 => 37,  50 => 36,  48 => 33,  47 => 32,  46 => 30,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@organisms/block/block.html.twig", "themes/custom/minim/source/03-organisms/block/block.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 30, "block" => 37, "include" => 40];
        static $filters = ["clean_class" => 32, "replace" => 33, "escape" => 39];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'block', 'include'],
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
