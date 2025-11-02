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

/* @organisms/header/header.html.twig */
class __TwigTemplate_07211050d89a102bdbb200fe2660c41c extends Template
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
            'site_header' => [$this, 'block_site_header'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield from $this->unwrap()->yieldBlock('site_header', $context, $blocks);
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["page"]);        yield from [];
    }

    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_site_header(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 2
        yield "  <header class=\"site-header header\">
    <div class=\"site-header__inner\">

      ";
        // line 5
        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "branding", [], "any", false, false, true, 5)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 6
            yield "        ";
            yield from $this->load("@organisms/branding/branding.html.twig", 6)->unwrap()->yield($context);
            // line 7
            yield "      ";
        }
        // line 8
        yield "
      ";
        // line 9
        if ((CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "navigation", [], "any", false, false, true, 9) || CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "focus", [], "any", false, false, true, 9))) {
            // line 10
            yield "        <input class=\"menu-button\" id=\"menu-button\" type=\"checkbox\" role=\"button\" />
        <label class=\"menu-icon\" for=\"menu-button\"><span class=\"menu-middle\"></span></label>
        <div class=\"site-header__nav-wrapper\">
          ";
            // line 13
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "navigation", [], "any", false, false, true, 13)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 14
                yield "            ";
                yield from $this->load("@organisms/navigation/navigation--main.html.twig", 14)->unwrap()->yield($context);
                // line 15
                yield "          ";
            }
            // line 16
            yield "          ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "focus", [], "any", false, false, true, 16)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 17
                yield "            ";
                yield from $this->load("@organisms/navigation/navigation--focus.html.twig", 17)->unwrap()->yield($context);
                // line 18
                yield "          ";
            }
            // line 19
            yield "        </div>
      ";
        }
        // line 21
        yield "
    </div>
  </header>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "@organisms/header/header.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  101 => 21,  97 => 19,  94 => 18,  91 => 17,  88 => 16,  85 => 15,  82 => 14,  80 => 13,  75 => 10,  73 => 9,  70 => 8,  67 => 7,  64 => 6,  62 => 5,  57 => 2,  45 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "@organisms/header/header.html.twig", "themes/custom/minim/source/03-organisms/header/header.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["block" => 1, "if" => 5, "include" => 6];
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
