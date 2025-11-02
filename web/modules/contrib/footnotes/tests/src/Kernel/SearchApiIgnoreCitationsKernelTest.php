<?php

namespace Drupal\Tests\footnotes\Kernel\Plugin\search_api\processor;

use Drupal\KernelTests\KernelTestBase;
use Drupal\footnotes\Plugin\search_api\processor\IgnoreCitations;

/**
 * Tests the IgnoreCitations Search API processor.
 *
 * @group search_api
 * @coversDefaultClass \Drupal\footnotes\Plugin\search_api\processor\IgnoreCitations
 */
class SearchApiIgnoreCitationsKernelTest extends KernelTestBase {

  /**
   * The Search API processor plugin instance.
   *
   * @var \Drupal\footnotes\Plugin\search_api\processor\IgnoreCitations
   */
  protected IgnoreCitations $processor;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'search_api',
    'footnotes',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Get the processor plugin from the manager.
    $processor_manager = $this->container->get('plugin.manager.search_api.processor');
    $this->processor = $processor_manager->createInstance('footnotes_ignore_citations');
  }

  /**
   * Helper function to normalize HTML strings for comparison.
   *
   * This attempts to remove cosmetic differences that might arise from
   * DOM processing, such as extra whitespace, and standardize entities.
   *
   * @param string $html
   *   The HTML string to normalize.
   *
   * @return string
   *   The normalized HTML string.
   */
  protected function normalizeHtml(string $html): string {
    // Remove XML declaration if present (e.g., from saveHTML).
    $html = preg_replace('/<\?xml[^?]*\?>\n?/i', '', $html);
    // Trim whitespace from the overall string.
    $html = trim($html);
    // Remove newlines, carriage returns, and tabs.
    $html = str_replace(["\r\n", "\r", "\n", "\t"], '', $html);
    // Remove whitespace between tags.
    $html = preg_replace('/>\s+</', '><', $html);
    // Collapse multiple spaces within text content into a single space.
    $html = preg_replace('/\s\s+/', ' ', $html);
    return $html;
  }

  /**
   * Tests the process() method of the IgnoreCitations processor.
   *
   * @dataProvider processDataProvider
   * @covers ::process
   */
  public function testProcess(string $input_html, string $expected_html, ?string $wrapper_class_override = NULL) {
    $config = $this->processor->getConfiguration();
    if ($wrapper_class_override !== NULL) {
      $config['wrapper_class'] = $wrapper_class_override;
    }
    elseif (!isset($config['wrapper_class'])) {
      $config['wrapper_class'] = 'footnote__citations-wrapper';
    }
    $this->processor->setConfiguration($config);

    // Use reflection to make the protected process() method accessible.
    $reflection_method = new \ReflectionMethod(IgnoreCitations::class, 'process');
    $reflection_method->setAccessible(TRUE);
    $value_to_process = $input_html;
    $reflection_method->invokeArgs($this->processor, [&$value_to_process]);

    $this->assertEquals($this->normalizeHtml($expected_html), $this->normalizeHtml($value_to_process));
  }

  /**
   * Data provider for testProcess().
   *
   * @return array
   *   An array of test cases, each test case is an array with:
   *   - input_html: The HTML string to process.
   *   - expected_html: The expected HTML string after processing.
   *   - wrapper_class (optional): The wrapper class to use for this test case.
   */
  public static function processDataProvider(): array {
    $default_wrapper = 'footnote__citations-wrapper';
    $custom_wrapper = 'my-custom-citation';

    return [
      'empty_string' => [
        'input_html' => '',
        'expected_html' => '',
      ],
      'no_citations_present' => [
        'input_html' => '<p>Some text without citations.</p><div>Hello</div>',
        'expected_html' => '<p>Some text without citations.</p><div>Hello</div>',
      ],
      'text_matching_default_wrapper_name_but_not_in_class_attribute' => [
        'input_html' => "<p>Some text mentions {$default_wrapper} but it's not a class.</p>",
        'expected_html' => "<p>Some text mentions {$default_wrapper} but it's not a class.</p>",
      ],
      'one_citation_default_wrapper' => [
        'input_html' => "<p>Text with <span class=\"{$default_wrapper}\">citation1</span> a citation.</p>",
      // Double space becomes single after normalization.
        'expected_html' => '<p>Text with a citation.</p>',
      ],
      'one_citation_default_wrapper_complex_class_attribute' => [
        'input_html' => "<p>Text with <span class=\"another-class {$default_wrapper} yet-another\">citation1</span> a citation.</p>",
        'expected_html' => '<p>Text with a citation.</p>',
      ],
      'two_citations_default_wrapper' => [
        'input_html' => "<p>Text <span class=\"{$default_wrapper}\">cite1</span> with <strong class=\"{$default_wrapper}\">cite2</strong> multiple.</p>",
        'expected_html' => '<p>Text with multiple.</p>',
      ],
      'nested_content_in_citation' => [
        'input_html' => "<div>Before <div class=\"{$default_wrapper}\"><p>Nested <strong>strong text</strong></p><span>More</span></div> After</div>",
        'expected_html' => '<div>Before After</div>',
      ],
      'citation_is_direct_child_of_implicit_body' => [
        'input_html' => "<span class=\"{$default_wrapper}\">Citation at root</span><p>Other content</p>",
        'expected_html' => '<p>Other content</p>',
      ],
      'partial_class_name_match_suffix' => [
        // The XPath `contains(@class, '$wrapperClass')` will match this.
        'input_html' => "<p>Text with <span class=\"{$default_wrapper}-suffix\">content</span>.</p>",
        'expected_html' => '<p>Text with .</p>',
      ],
      'partial_class_name_match_prefix' => [
        // The XPath `contains(@class, '$wrapperClass')` will match this.
        'input_html' => "<p>Text with <span class=\"prefix-{$default_wrapper}\">content</span>.</p>",
        'expected_html' => '<p>Text with .</p>',
      ],
      'exact_class_among_others_should_be_removed' => [
        'input_html' => "<p>Text <span class=\"other {$default_wrapper} another\">citation</span> here.</p>",
        'expected_html' => '<p>Text here.</p>',
      ],
      'custom_wrapper_class_test' => [
        'input_html' => "<p>Custom <span class=\"{$custom_wrapper}\">custom1</span> citation. Standard <span class=\"{$default_wrapper}\">std1</span>.</p>",
        'expected_html' => "<p>Custom citation. Standard <span class=\"{$default_wrapper}\">std1</span>.</p>",
        'wrapper_class_override' => $custom_wrapper,
      ],
      'custom_wrapper_class_multiple_citations' => [
        'input_html' => "<p>Custom <span class=\"{$custom_wrapper}\">c1</span> and <strong class=\"{$custom_wrapper}\">c2</strong>.</p>",
        'expected_html' => '<p>Custom and .</p>',
        'wrapper_class_override' => $custom_wrapper,
      ],
      'html_entities_in_content_and_citation' => [
        // &amp; becomes &, &lt; becomes <, &gt; becomes > after final
        // html_entity_decode.
        'input_html' => "<p>Text with &amp; &lt; &gt; <span class=\"{$default_wrapper}\">citation &amp; stuff</span> more.</p>",
        'expected_html' => '<p>Text with & < > more.</p>',
      ],
      'script_tags_outside_citation_preserved' => [
        'input_html' => "<p>Hello</p><script>alert(\"foo\");</script><div class=\"{$default_wrapper}\"><p>citation</p></div><script>alert(\"bar\");</script>",
        'expected_html' => '<p>Hello</p><script>alert("foo");</script><script>alert("bar");</script>',
      ],
      'script_tags_inside_citation_removed' => [
        'input_html' => "<p>Hello</p><div class=\"{$default_wrapper}\"><p>citation</p><script>alert(\"danger\");</script></div><p>World</p>",
        'expected_html' => '<p>Hello</p><p>World</p>',
      ],
      'input_contains_xml_declaration' => [
        'input_html' => "<?xml encoding=\"UTF-8\"><p>Text with <span class=\"{$default_wrapper}\">citation1</span> a citation.</p>",
        'expected_html' => '<p>Text with a citation.</p>',
      ],
      'value_without_configured_wrapper_class_triggers_early_return' => [
        'input_html' => '<p>This content does not contain the specific target class.</p>',
        'expected_html' => '<p>This content does not contain the specific target class.</p>',
        'wrapper_class_override' => 'a-very-unique-class-not-in-html',
      ],
    ];
  }

}
