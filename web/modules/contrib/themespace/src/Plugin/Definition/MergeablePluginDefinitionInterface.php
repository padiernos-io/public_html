<?php

namespace Drupal\themespace\Plugin\Definition;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;

/**
 * Plugin definitions that can be merged with another definition.
 *
 * Interface for plugin definitions which can be merged with other plugin
 * definitions. This is useful for derivative definitions which can be
 * merged with a base definition, or have defaults applied.
 *
 * It is up to the implementing plugin definition to determine how the merge
 * applies values, and which values can be overridden.
 *
 * @see \Drupal\themespace\Plugin\Discovery\ProviderTypedDeriverDiscoveryDecorator::mergeDerivativeDefinition()
 */
interface MergeablePluginDefinitionInterface extends PluginDefinitionInterface {

  /**
   * Merge information from another plugin definition into this one.
   *
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $definition
   *   Other plugin definition to merge into this one. Commonly this is a base
   *   plugin definition being applied to a derived definition.
   */
  public function mergeDefinition(array|PluginDefinitionInterface $definition): PluginDefinitionInterface|array;

}
