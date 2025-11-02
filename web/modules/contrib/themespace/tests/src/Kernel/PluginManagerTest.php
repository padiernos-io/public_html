<?php

namespace Drupal\Tests\themespace\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\themespace\Traits\ThemeInstallTrait;

/**
 * Test for verifying provider typed plugin manager and trait functionality.
 *
 * @group themespace
 */
class PluginManagerTest extends KernelTestBase {

  use ThemeInstallTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'themespace',
    'themespace_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installTheme([
      'themespace_test_theme',
      'themespace_test_subtheme',
    ]);
  }

  /**
   * Ensure that plugin manager is aware of base theme definitions.
   *
   * When fetching plugins by current or active theme, be sure that the plugin
   * includes any base theme plugin implementations as well.
   */
  public function testBaseThemePlugin(): void {
    $pluginManager = $this->container->get('plugin.manager.themespace_attribute_test');
    $themeDefs = $pluginManager->getDefinitionsByTheme('themespace_test_theme');
    $subThemeDefs = $pluginManager->getDefinitionsByTheme('themespace_test_subtheme');

    // Ensure that all the plugins discovered for the base theme are included
    // in the plugins returned for the sub-theme.
    $this->assertEmpty(array_diff_key($themeDefs, $subThemeDefs));

    // Check that only 1 additional sub theme definition is the only difference
    // between the base theme and the sub theme.
    $subThemeDiff = array_diff_key($subThemeDefs, $themeDefs);
    $this->assertCount(1, $subThemeDiff, sprintf('Subtheme should only add one definition %s differences found', count($subThemeDiff)));
    $this->assertArrayHasKey('subtheme.test.attribute', $subThemeDiff);
  }

  /**
   * Ensure that plugin manager can instatiate plugins from theme namespaces.
   *
   * @doesNotPerformAssertions
   */
  public function testCreateAttributePluginInstances(): void {
    $pluginManager = $this->container->get('plugin.manager.themespace_attribute_test');

    // YAML definition, one from a theme namespace and one referencing a module.
    $pluginManager->createInstance('theme.test.yaml');
    $pluginManager->createInstance('theme.test1.yaml');

    // Attribute theme definition.
    $pluginManager->createInstance('subtheme.test.attribute');

    // Module attribute and YAML definitions.
    $pluginManager->createInstance('module.test.attribute');
    $pluginManager->createInstance('module.test.yaml');
  }

  /**
   * Test a plugin manager which has no plugin definitions.
   *
   * Ensure the return is still an empty array and does not throw errors.
   */
  public function testEmptyPlugins(): void {
    $pluginManager = $this->container->get('plugin.manager.themespace_empty');
    $moduleDefs = $pluginManager->getModuleDefinitions();
    $themeDefs = $pluginManager->getDefinitionsByTheme('themespace_test_theme');

    // Do not need to assert that these are arrays because the method return
    // type already defines these are arrays. Would throw PHP error otherwise.
    $this->assertEmpty($moduleDefs);
    $this->assertEmpty($themeDefs);
  }

}
