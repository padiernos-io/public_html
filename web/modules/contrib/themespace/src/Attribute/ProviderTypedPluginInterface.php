<?php

namespace Drupal\themespace\Attribute;

use Drupal\Component\Plugin\Attribute\AttributeInterface;

/**
 * Attribute interface for plugins which know their provider's extension type.
 *
 * The themespace module adds theme namespaces to the Drupal class loader and
 * container namespaces, which allows plugins to be discovered in themes. With
 * the addition of plugin in themes, plugin definitions need to be identified as
 * belonging to a module or theme.
 *
 * Plugin manager implementations should use this information and be able to
 * filter theme plugins based on the active theme.
 *
 * This attribute is meant to add the extension type ("module" or "theme")
 * information to the plugin attribute in the "provider_type" property key.
 */
interface ProviderTypedPluginInterface extends AttributeInterface {

  /**
   * Get the provider's extension type value if it is available.
   *
   * @return string|null
   *   Should return either "module" or "theme" depending on if the provider is
   *   a module (or profile) or a theme. NULL if it has not been set, or cannot
   *   be determined.
   */
  public function getProviderType(): ?string;

  /**
   * Set the attribute provider's extension type value.
   *
   * @param string $extensionType
   *   Either "module" or "theme" to indicate the provider's extension type.
   */
  public function setProviderType(string $extensionType): void;

}
