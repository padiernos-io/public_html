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

/* themes/custom/minim/source/03-organisms/menu/menu--main.html.twig */
class __TwigTemplate_24a1ab9389c5b0b71e406c3de2314add extends Template
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
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 2
        yield "
";
        // line 3
        $context["classes"] = ["navigation-main", "no-bullets", "grid"];
        // line 8
        yield "
";
        // line 9
        $macros["menus"] = $this->macros["menus"] = $this;
        // line 10
        yield "
";
        // line 15
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($macros["menus"]->getTemplateForMacro("macro_menu_links", $context, 15, $this->getSourceContext())->macro_menu_links(...[($context["items"] ?? null), ($context["attributes"] ?? null), ($context["classes"] ?? null), 0]));
        yield "

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["_self", "items", "attributes", "menu_level"]);        yield from [];
    }

    // line 17
    public function macro_menu_links($items = null, $attributes = null, $classes = null, $menu_level = null, ...$varargs): string|Markup
    {
        $macros = $this->macros;
        $context = [
            "items" => $items,
            "attributes" => $attributes,
            "classes" => $classes,
            "menu_level" => $menu_level,
            "varargs" => $varargs,
        ] + $this->env->getGlobals();

        $blocks = [];

        return ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 18
            yield "  ";
            $context["primary_nav_level"] = ("menu--level-" . (($context["menu_level"] ?? null) + 1));
            // line 19
            yield "  ";
            $macros["menus"] = $this;
            // line 20
            yield "  ";
            if ((($tmp = ($context["items"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 21
                yield "    ";
                if ((($context["menu_level"] ?? null) == 0)) {
                    // line 22
                    yield "      <ul";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null), "menu", ($context["primary_nav_level"] ?? null)], "method", false, false, true, 22), "html", null, true);
                    yield ">
    ";
                } else {
                    // line 24
                    yield "      <ul class=\"menu\">
    ";
                }
                // line 26
                yield "    ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                    // line 27
                    yield "      ";
                    // line 28
                    $context["menu_classes"] = ["menu-item", (((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 30
$context["item"], "is_expanded", [], "any", false, false, true, 30)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("menu-item--expanded") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 31
$context["item"], "is_collapsed", [], "any", false, false, true, 31)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("menu-item--collapsed") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,                     // line 32
$context["item"], "in_active_trail", [], "any", false, false, true, 32)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("menu-item--active-trail") : (""))];
                    // line 35
                    yield "
      ";
                    // line 37
                    $context["nav_classes"] = ["nav-link"];
                    // line 41
                    yield "
      <li";
                    // line 42
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["item"], "attributes", [], "any", false, false, true, 42), "addClass", [($context["menu_classes"] ?? null)], "method", false, false, true, 42), "html", null, true);
                    yield ">
        ";
                    // line 43
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->addClass($this->extensions['Drupal\Core\Template\TwigExtension']->getLink(CoreExtension::getAttribute($this->env, $this->source, $context["item"], "title", [], "any", false, false, true, 43), CoreExtension::getAttribute($this->env, $this->source, $context["item"], "url", [], "any", false, false, true, 43)), ($context["nav_classes"] ?? null)), "html", null, true);
                    yield "
        ";
                    // line 44
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 44)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 45
                        yield "
          ";
                        // line 46
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar($macros["menus"]->getTemplateForMacro("macro_menu_links", $context, 46, $this->getSourceContext())->macro_menu_links(...[CoreExtension::getAttribute($this->env, $this->source, $context["item"], "below", [], "any", false, false, true, 46), ($context["attributes"] ?? null), (($context["menu_level"] ?? null) + 1)]));
                        yield "
        ";
                    }
                    // line 48
                    yield "      </li>

    ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['item'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 51
                yield "
    </ul>
  ";
            }
            // line 54
            yield "
";
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "themes/custom/minim/source/03-organisms/menu/menu--main.html.twig";
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
        return array (  154 => 54,  149 => 51,  141 => 48,  136 => 46,  133 => 45,  131 => 44,  127 => 43,  123 => 42,  120 => 41,  118 => 37,  115 => 35,  113 => 32,  112 => 31,  111 => 30,  110 => 28,  108 => 27,  103 => 26,  99 => 24,  93 => 22,  90 => 21,  87 => 20,  84 => 19,  81 => 18,  66 => 17,  57 => 15,  54 => 10,  52 => 9,  49 => 8,  47 => 3,  44 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "themes/custom/minim/source/03-organisms/menu/menu--main.html.twig", "/home/padiernos/public_html/web/themes/custom/minim/source/03-organisms/menu/menu--main.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 3, "import" => 9, "macro" => 17, "if" => 20, "for" => 26];
        static $filters = ["escape" => 22, "add_class" => 43];
        static $functions = ["link" => 43];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'import', 'macro', 'if', 'for'],
                ['escape', 'add_class'],
                ['link'],
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
