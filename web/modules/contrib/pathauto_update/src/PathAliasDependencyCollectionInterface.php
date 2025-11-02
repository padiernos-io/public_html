<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityInterface;

/**
 * A collection of path alias dependencies.
 */
interface PathAliasDependencyCollectionInterface {

  /**
   * Get the dependent entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The entities.
   */
  public function getEntities(): array;

  /**
   * Get the dependent configs.
   *
   * @return \Drupal\Core\Config\Config[]
   *   The configs.
   */
  public function getConfigs(): array;

  /**
   * Add a dependent entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function addEntity(EntityInterface $entity): void;

  /**
   * Add a dependent config.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The config.
   */
  public function addConfig(Config $config): void;

}
