<?php

namespace Drupal\themespace\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Plugin manager interface that supports split of theme and module definitions.
 */
interface ProviderTypedPluginManagerInterface extends PluginManagerInterface {

  /**
   * Get plugin definitions that are provided by modules.
   *
   * @return array
   *   An array of plugin definitions that are provided by modules. Definitions
   *   are keyed by their plugin IDs.
   */
  public function getModuleDefinitions(): array;

  /**
   * Get plugin definitions provided by the requested theme.
   *
   * @param string $theme
   *   Machine name of theme to fetch the plugin definitions for.
   *
   * @return array
   *   An array of plugin definitions that were provided by the requested theme.
   *   Definitions are keyed by their plugin IDs. An empty array can be returned
   *   if there were no plugins provided by the requested theme.
   */
  public function getDefinitionsByTheme(string $theme): array;

}
