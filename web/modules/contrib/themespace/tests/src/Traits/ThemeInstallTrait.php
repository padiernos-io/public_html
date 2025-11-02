<?php

namespace Drupal\Tests\themespace\Traits;

use Drupal\Core\Extension\ModuleExtensionList;

/**
 * Test trait for Kernel test that need to install themes.
 *
 * This trait mocks the "extension.list.module" service so the
 * \Drupal\Core\Extension\ModuleExtensionList::getList() only returns active
 * modules.
 *
 * This avoids an issue where other tests have modules with deprecations
 * (sometimes purposeful like simpletest_deprecation_test) from preventing
 * the theme installer service from working correctly.
 */
trait ThemeInstallTrait {

  /**
   * Installs theme(s) for themespace Kernel tests.
   *
   * @param string[]|string $themes
   *   Either the machine name of a single theme, or an array of theme machine
   *   names to install.
   */
  protected function installTheme(array|string $themes): void {
    $infoParser = $this->container->get('info_parser');
    $moduleList = $this->container->get('module_handler')->getModuleList();

    // Create module extensions for only the active modules enabled by for the
    // test. This avoids the info parser from erroring on the
    // "core_version_requirement" that might be purposefully there for
    // deprecation testing (ex: simpletest_deprecation_test.info.yml).
    foreach ($moduleList as $extension) {
      /** @var \Drupal\Core\Extension\Extension|object $extension */
      $extension->info = $infoParser->parse($extension->getPathname());
    }

    // Create a mock "extension.list.module" service so as not to trigger any
    // the InfoParserException mentioned above, while installing themes.
    $extensionList = $this->createMock(ModuleExtensionList::class);
    $extensionList->expects($this->any())
      ->method('getList')
      ->will($this->returnValue($moduleList));

    $this->container->set('extension.list.module', $extensionList);

    if (!is_array($themes)) {
      $themes = [$themes];
    }

    $this->container
      ->get('theme_installer')
      ->install($themes);
  }

  /**
   * Sets a theme to be the active theme.
   *
   * Theme should be installed using static::installTheme() before setting
   * it as active.
   *
   * @param string $theme
   *   Machine name of the theme to make the active theme.
   */
  protected function setActiveTheme(string $theme): void {
    $activeTheme = $this->container
      ->get('theme.initialization')
      ->initTheme($theme);

    $this->container
      ->get('theme.manager')
      ->setActiveTheme($activeTheme);
  }

}
