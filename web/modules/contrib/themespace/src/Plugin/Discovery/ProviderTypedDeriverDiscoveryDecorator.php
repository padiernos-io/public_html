<?php

namespace Drupal\themespace\Plugin\Discovery;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\themespace\Plugin\Definition\MergeablePluginDefinitionInterface;

/**
 * Plugin discovery decorator which adds derivative definitions.
 *
 * This version of the deriver discovery decorator just implements the
 * \Drupal\themespace\Plugin\Discovery\ProviderTypedDiscoveryInterface by adding
 * the ::getModuleDefinitions() and ::getThemeDefinition() methods.
 */
class ProviderTypedDeriverDiscoveryDecorator extends ContainerDerivativeDiscoveryDecorator implements ProviderTypedDiscoveryInterface {

  /**
   * {@inheritdoc}
   */
  protected function mergeDerivativeDefinition($baseDefinition, $derivativeDefinition): PluginDefinitionInterface|array {
    if (is_array($baseDefinition) && is_array($derivativeDefinition)) {
      return parent::mergeDerivativeDefinition($baseDefinition, $derivativeDefinition);
    }
    elseif ($derivativeDefinition instanceof MergeablePluginDefinitionInterface) {
      $derivativeDefinition->mergeDefinition($baseDefinition);
    }

    // If definitions are not arrays and are plugin objects, and there is no
    // clear way to merge them, just return the derivative definition.
    return $derivativeDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDefinitions(): array {
    if ($this->decorated instanceof ProviderTypedDiscoveryInterface) {
      $pluginDefs = $this->decorated->getModuleDefinitions();
      return $this->getDerivatives($pluginDefs);
    }

    // If decorated is not provider type aware, treat all plugin definitions
    // as if they are "module" provided definitions, getDefinitions() method.
    return parent::getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeDefinitions(): array {
    if ($this->decorated instanceof ProviderTypedDiscoveryInterface) {
      $pluginDefs = $this->decorated->getThemeDefinitions();
      return $this->getDerivatives($pluginDefs);
    }

    // If decorated discovery is not provider type aware, treat all definitions
    // as if they are "module" provided definitions - so there would be no
    // theme definition to create derivatives for.
    return [];
  }

}
