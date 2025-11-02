<?php

namespace Drupal\themespace\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Base plugin attribute for ProviderTypedPlugin classes.
 */
class ProviderTypedPlugin extends Plugin implements ProviderTypedPluginInterface {

  /**
   * The provider type (module or theme) of the attribute class.
   */
  protected string|null $providerType = NULL;

  /**
   * {@inheritdoc}
   */
  public function getProviderType(): ?string {
    return $this->providerType;
  }

  /**
   * {@inheritdoc}
   */
  public function setProviderType(?string $extensionType = NULL): void {
    $this->providerType = $extensionType;
  }

}
