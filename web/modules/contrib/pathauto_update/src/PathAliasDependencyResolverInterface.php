<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Entity\EntityInterface;

/**
 * Resolves dependencies of pathauto patterns.
 */
interface PathAliasDependencyResolverInterface {

  /**
   * Collect dependencies of a pathauto pattern generated for a certain entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to collect dependencies.
   */
  public function getDependencies(EntityInterface $entity): PathAliasDependencyCollectionInterface;

}
