<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityInterface;

/**
 * A collection of path alias dependencies.
 */
class PathAliasDependencyCollection implements PathAliasDependencyCollectionInterface {

  /**
   * The entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected array $entities = [];

  /**
   * The configs.
   *
   * @var \Drupal\Core\Config\Config[]
   */
  protected array $configs = [];

  /**
   * {@inheritdoc}
   */
  public function getEntities(): array {
    return $this->entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigs(): array {
    return $this->configs;
  }

  /**
   * {@inheritdoc}
   */
  public function addEntity(EntityInterface $entity): void {
    $key = implode('.', [
      $entity->getEntityTypeId(),
      $entity->id(),
      $entity->language()->getId(),
    ]);
    $this->entities[$key] = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function addConfig(Config $config): void {
    $this->configs[$config->getName()] = $config;
  }

}
