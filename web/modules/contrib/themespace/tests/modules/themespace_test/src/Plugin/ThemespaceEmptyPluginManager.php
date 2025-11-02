<?php

namespace Drupal\themespace_test\Plugin;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscovery;
use Drupal\themespace\Plugin\ProviderTypedPluginManager;
use Drupal\themespace\Plugin\ProviderTypedPluginManagerInterface;

/**
 * Test plugin manager to test the ProviderTypedPluginManagerTrait.
 */
class ThemespaceEmptyPluginManager extends ProviderTypedPluginManager implements ProviderTypedPluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery(): DiscoveryInterface {
    if (!$this->discovery) {
      $this->discovery = new ProviderTypedYamlDiscovery(
        'themespace_empty',
        $this->moduleHandler->getModuleDirectories(),
        $this->themeHandler->getThemeDirectories()
      );
    }
    return $this->discovery;
  }

}
