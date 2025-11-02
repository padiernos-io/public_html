<?php

namespace Drupal\pathauto_update;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Provides dependencies for tokens.
 */
interface PatternTokenDependencyProviderInterface extends PluginInspectionInterface {

  /**
   * Add dependencies from tokens.
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void;

}
