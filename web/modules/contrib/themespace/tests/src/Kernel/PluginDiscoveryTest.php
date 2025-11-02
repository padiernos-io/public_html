<?php

namespace Drupal\Tests\themespace\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\themespace\Traits\ThemeInstallTrait;
use Drupal\themespace\Plugin\Discovery\ProviderTypedAttributeClassDiscovery;
use Drupal\themespace\Plugin\Discovery\ProviderTypedDeriverDiscoveryDecorator;
use Drupal\themespace\Plugin\Discovery\ProviderTypedDiscoveryInterface;
use Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscovery;
use Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscoveryDecorator;
use Drupal\themespace_test\Attribute\ThemespaceTest;
use Drupal\themespace_test\Plugin\TestPluginDefinition;

/**
 * Test the provider typed plugin discovery.
 *
 * @group themespace
 */
class PluginDiscoveryTest extends KernelTestBase {

  use ThemeInstallTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'themespace',
    'themespace_test',
  ];

  /**
   * Expected plugin definitions keyed by provider type and discovery.
   *
   * The expected lists of plugins to be found with discovery tests from
   * the themespace_test module and the themespace_test_theme theme extensions.
   *
   * @var array
   */
  protected static $expectedDefinitions = [
    'module' => [
      'attribute' => ['module.test.attribute'],
      'additional' => ['module.test.additional'],
      'yaml' => [
        'module.test.yaml',
        'module.deriver.yaml',
      ],
      'deriver' => [
        'module.test.yaml',
        'module.deriver.yaml:derived',
      ],
    ],
    'theme' => [
      'attribute' => ['theme.test.attribute'],
      'yaml' => [
        'theme.test.yaml',
        'theme.test1.yaml',
      ],
      'deriver' => [
        'theme.test.yaml',
        'theme.test1.yaml',
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installTheme('themespace_test_theme');
  }

  /**
   * Asserts that the discovery found plugin and verifies definitions.
   *
   * @param \Drupal\themespace\Plugin\Discovery\ProviderTypedDiscoveryInterface $discovery
   *   The discovery to use for finding plugin definitions.
   * @param array $types
   *   An array of the discovery types expected from this discovery being used
   *   (a combination of "yaml" or "attribute").
   */
  protected function assertDiscoveredDefinitions(ProviderTypedDiscoveryInterface $discovery, array $types): void {
    // Create the list of expected definitions based on the plugin types.
    // These are the plugins that we expect the discovery method to find.
    $expected = [
      'module' => [],
      'theme' => [],
    ];
    foreach ($types as $defType) {
      $expected['module'] = array_merge($expected['module'], static::$expectedDefinitions['module'][$defType] ?? []);
      $expected['theme'] = array_merge($expected['theme'], static::$expectedDefinitions['theme'][$defType] ?? []);
    }

    // Get the definitions using the discovery object and compare to our
    // expected module and theme definitions.
    $allDefinitions = $discovery->getDefinitions();
    $groupedPlugins = [
      'module' => $discovery->getModuleDefinitions(),
      'theme' => $discovery->getThemeDefinitions(),
    ];

    foreach ($groupedPlugins as $providerType => $plugins) {
      $this->assertEqualsCanonicalizing($expected[$providerType], array_keys($plugins));

      // Ensure that each definition exists in the "all" definitions and that
      // the definition reports the expected provider type.
      foreach ($plugins as $id => $def) {
        $this->assertArrayHasKey($id, $allDefinitions);
        $this->assertEquals($providerType, $def->getProviderType());
      }
    }
  }

  /**
   * Test attribute class discovery with provider typed plugins.
   */
  public function testAttributeDiscovery(): void {
    $discovery = new ProviderTypedAttributeClassDiscovery(
      'Plugin/Themespace',
      $this->container->get('themespace.namespaces'),
      ThemespaceTest::class
    );

    $this->assertDiscoveredDefinitions($discovery, ['attribute']);
  }

  /**
   * Test attribute discovery when multiple directory suffixes provided.
   *
   * Allow attribute discovery to search for definitions in multiple
   * subdirectories per namespace.
   */
  public function testMultipleDirectoryDiscovery(): void {
    $discovery = new ProviderTypedAttributeClassDiscovery(
      ['Plugin/Themespace', 'Additional'],
      $this->container->get('themespace.namespaces'),
      ThemespaceTest::class
    );

    $this->assertDiscoveredDefinitions($discovery, ['attribute', 'additional']);
  }

  /**
   * Test plugin definitions using YAML discovery.
   */
  public function testYamlDiscovery(): void {
    $discovery = new ProviderTypedYamlDiscovery(
      'themespace_test',
      $this->container->get('module_handler')->getModuleDirectories(),
      $this->container->get('theme_handler')->getThemeDirectories()
    );

    // Set the plugin definition class for the YAML discovery, as this tests
    // the array representation and the plugin definition class handling.
    $discovery->setPluginDefinitionClass(TestPluginDefinition::class);

    $this->assertDiscoveredDefinitions($discovery, ['yaml']);
  }

  /**
   * Test plugin definitions using the YAML discovery decorator.
   */
  public function testYamlDiscoveryDecorator(): void {
    $moduleHandler = $this->container->get('module_handler');
    $themeHandler = $this->container->get('theme_handler');

    $discovery = new ProviderTypedAttributeClassDiscovery(
      'Plugin/Themespace',
      $this->container->get('themespace.namespaces'),
      ThemespaceTest::class
    );
    $discovery = new ProviderTypedYamlDiscoveryDecorator(
      $discovery,
      'themespace_test',
      $moduleHandler->getModuleDirectories(),
      $themeHandler->getThemeDirectories(),
      TestPluginDefinition::class
    );

    // @todo create test which test when a non-provider typed discovery object
    // is being decorated.
    $this->assertDiscoveredDefinitions($discovery, ['yaml', 'attribute']);
  }

  /**
   * Test plugin definition deriver using deriver discovery decorator.
   *
   * Tests plugin definitions by decorating the YAML discovery. The module
   * definition "module.deriver.yaml" should be replaced by derived definitions.
   */
  public function testDeriverDiscoveryDecorator(): void {
    $moduleHandler = $this->container->get('module_handler');
    $themeHandler = $this->container->get('theme_handler');

    $discovery = new ProviderTypedYamlDiscovery(
      'themespace_test',
      $moduleHandler->getModuleDirectories(),
      $themeHandler->getThemeDirectories(),
      TestPluginDefinition::class
    );
    $discovery = new ProviderTypedDeriverDiscoveryDecorator($discovery);

    $this->assertDiscoveredDefinitions($discovery, ['deriver']);
  }

}
