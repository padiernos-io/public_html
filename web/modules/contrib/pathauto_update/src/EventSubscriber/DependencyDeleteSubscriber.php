<?php

namespace Drupal\pathauto_update\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyInterface;
use Drupal\pathauto_update\PathAliasDependencyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Delete dependencies when entities are deleted.
 */
class DependencyDeleteSubscriber implements EventSubscriberInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * The entity alias dependency repository.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyRepository
   */
  protected PathAliasDependencyRepository $repository;

  public function __construct(
    Connection $database,
    PathAliasDependencyRepository $repository
  ) {
    $this->database = $database;
    $this->repository = $repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::DELETE][] = ['onConfigDelete'];

    return $events;
  }

  /**
   * Delete dependencies when a config entity is deleted.
   */
  public function onConfigDelete(ConfigCrudEvent $event): void {
    $this->repository->deleteDependenciesByType(
      PathAliasDependencyInterface::TYPE_CONFIG,
      $event->getConfig()->getName()
    );
  }

  /**
   * Delete dependencies when an entity is deleted.
   */
  public function onEntityDelete(EntityInterface $entity): void {
    $this->repository->deleteDependenciesByType(
      PathAliasDependencyInterface::TYPE_ENTITY,
      implode(':', [
        $entity->getEntityTypeId(),
        $entity->id(),
        $entity->language()->getId(),
      ])
    );

    if ($entity instanceof PathAliasInterface) {
      $this->repository->deleteDependenciesByPathAlias($entity);
    }
  }

  /**
   * Check if the path_alias_dependency table exists.
   */
  protected function isSchemaInstalled(): bool {
    return $this->database->schema()
      ->tableExists('path_alias_dependency');
  }

}
