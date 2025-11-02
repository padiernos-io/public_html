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

/* @symbiosis/page/page.html.twig */
class __TwigTemplate_1d348f7c22a0f9aa00d191b35592fde9 extends Template
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
            'banner' => [$this, 'block_banner'],
            'main' => [$this, 'block_main'],
            'hero' => [$this, 'block_hero'],
            'footer' => [$this, 'block_footer'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 45
        yield "
";
        // line 46
        yield from $this->unwrap()->yieldBlock('banner', $context, $blocks);
        // line 51
        yield "
";
        // line 52
        yield from $this->unwrap()->yieldBlock('main', $context, $blocks);
        // line 66
        yield "
";
        // line 67
        yield from $this->unwrap()->yieldBlock('footer', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["page"]);        yield from [];
    }

    // line 46
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_banner(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 47
        yield "  ";
        if (((CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "branding", [], "any", false, false, true, 47) || CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "navigation", [], "any", false, false, true, 47)) || CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "header", [], "any", false, false, true, 47))) {
            // line 48
            yield "    ";
            yield from $this->load("@organisms/header/header.html.twig", 48)->unwrap()->yield($context);
            // line 49
            yield "  ";
        }
        yield from [];
    }

    // line 52
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_main(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 53
        yield "  <main role=\"main\" class=\"site-main main";
        if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "hero", [], "any", false, false, true, 53)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " no-hero";
        }
        yield "\">
    <a id=\"main-content\" tabindex=\"-1\" class=\"visually-hidden\"></a>

    ";
        // line 56
        yield from $this->unwrap()->yieldBlock('hero', $context, $blocks);
        // line 61
        yield "
    ";
        // line 62
        yield from $this->load("@organisms/main/main.html.twig", 62)->unwrap()->yield($context);
        // line 63
        yield "
  </main>
";
        yield from [];
    }

    // line 56
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_hero(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 57
        yield "      ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "hero", [], "any", false, false, true, 57)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 58
            yield "        ";
            yield from $this->load("@organisms/hero/hero.html.twig", 58)->unwrap()->yield($context);
            // line 59
            yield "      ";
        }
        // line 60
        yield "    ";
        yield from [];
    }

    // line 67
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_footer(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 68
        yield "  ";
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "footer", [], "any", false, false, true, 68)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 69
            yield "    ";
            yield from $this->load("@organisms/footer/footer.html.twig", 69)->unwrap()->yield($context);
            // line 70
            yield "  ";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@symbiosis/page/page.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  150 => 70,  147 => 69,  144 => 68,  137 => 67,  132 => 60,  129 => 59,  126 => 58,  123 => 57,  116 => 56,  109 => 63,  107 => 62,  104 => 61,  102 => 56,  93 => 53,  86 => 52,  80 => 49,  77 => 48,  74 => 47,  67 => 46,  61 => 67,  58 => 66,  56 => 52,  53 => 51,  51 => 46,  48 => 45,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@symbiosis/page/page.html.twig", "themes/custom/minim/source/04-symbiosis/page/page.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["block" => 46, "if" => 47, "include" => 48];
        static $filters = [];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['block', 'if', 'include'],
                [],
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
