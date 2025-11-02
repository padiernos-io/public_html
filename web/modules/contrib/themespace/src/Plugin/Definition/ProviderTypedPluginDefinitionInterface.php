<?php

namespace Drupal\themespace\Plugin\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;

/**
 * Interface for plugin definitions which know their provider's extension type.
 */
interface ProviderTypedPluginDefinitionInterface extends PluginDefinitionInterface {

  /**
   * Get the provider's extension type ("module" or "theme").
   *
   * @return string|null
   *   Get the plugin's provider extension type information if available. If
   *   the provider type is not available, NULL is returned.
   */
  public function getProviderType(): ?string;

}
