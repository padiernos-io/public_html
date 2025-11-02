<?php

namespace Drupal\Tests\themespace\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\themespace\Traits\ThemeInstallTrait;

/**
 * Test the theme namespaces addition and removal.
 *
 * @group themespace
 */
class ThemeNamespaceTest extends KernelTestBase {

  use ThemeInstallTrait;

  /**
   * The expected namespace for the test theme.
   */
  const THEME_NAMESPACE = 'Drupal\\themespace_test_theme';

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

    $this->installTheme('themespace_test_theme');
  }

  /**
   * Ensure that theme namespaces are being added with the expected pattern.
   *
   * The theme namespace should be "\Drupal\Theme\<theme_name>" and that the
   * namespace references the expected directory.
   */
  public function testThemeNamespaces(): void {
    $namespaces = $this->container->getParameter('container.themespaces');
    $themePath = $this->container
      ->get('extension.list.theme')
      ->getPathname('themespace_test_theme');

    // Ensure namespace exists and points to the expected directory.
    $this->assertArrayHasKey(static::THEME_NAMESPACE, $namespaces);
    $this->assertEquals(dirname($themePath) . '/src', $namespaces[static::THEME_NAMESPACE]);

    // Check known class to ensure class autoloader is able to locate the
    // classes defined in the theme namespace.
    $themePluginClass = static::THEME_NAMESPACE . '\\Plugin\\Themespace\\ThemeAttributePlugin';
    $this->assertTrue(class_exists($themePluginClass), 'Classes in "Themespace Test Theme" are not seen by autoloader');
  }

  /**
   * Ensure that namespaces are removed when themes are uninstalled.
   *
   * Modules generally get namespaces cleared when they are removed, but themes
   * don't have that expectation of having namespaces to remove.
   */
  public function testUninstallNamespaces():void {
    $this->container
      ->get('theme_installer')
      ->uninstall(['themespace_test_theme']);

    // Ensure namespace is removed after theme has been uninstalled.
    $namespaces = $this->container->getParameter('container.themespaces');
    $this->assertArrayNotHasKey(static::THEME_NAMESPACE, $namespaces, 'Test theme namespace was not removed after theme was uninstalled.');
  }

}
