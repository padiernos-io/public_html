<?php

namespace Drupal\themespace\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;

/**
 * Plugin discovery interface which supports module and theme plugins.
 *
 * Supports finding definitions for only modules, or only themes in addition
 * to the normal
 * \Drupal\Component\Plugin\Discovery\DiscoveryInterface::getDefinitions()
 * method.
 */
interface ProviderTypedDiscoveryInterface extends DiscoveryInterface {

  /**
   * Gets all plugin definitions from modules.
   *
   * @return array
   *   Plugin definitions found in modules. Array is keyed by the plugin ID.
   */
  public function getModuleDefinitions(): array;

  /**
   * Gets all plugin definitions from themes.
   *
   * @return array
   *   Plugin definitions found in themes. Array is keyed by the plugin ID.
   */
  public function getThemeDefinitions(): array;

}
