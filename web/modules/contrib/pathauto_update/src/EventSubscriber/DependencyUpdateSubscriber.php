<?php

namespace Drupal\pathauto_update\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Entity\EntityInterface;
use Drupal\pathauto_update\PathAliasDependencyInterface;
use Drupal\pathauto_update\PathAliasDependencyRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Update path aliases when dependent entities are updated.
 */
class DependencyUpdateSubscriber implements EventSubscriberInterface {

  /**
   * The path alias dependency repository.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyRepository
   */
  protected PathAliasDependencyRepository $repository;

  public function __construct(
    PathAliasDependencyRepository $repository
  ) {
    $this->repository = $repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onConfigUpdate'];

    return $events;
  }

  /**
   * Update path aliases depending on the updated configuration.
   */
  public function onConfigUpdate(ConfigCrudEvent $event): void {
    $this->repository->updatePathAliasesByType(
      PathAliasDependencyInterface::TYPE_CONFIG,
      $event->getConfig()->getName()
    );
  }

  /**
   * Update path aliases depending on the updated entity.
   */
  public function onEntityUpdate(EntityInterface $entity): void {
    $this->repository->updatePathAliasesByType(
      PathAliasDependencyInterface::TYPE_ENTITY,
      implode(':', [
        $entity->getEntityTypeId(),
        $entity->id(),
        $entity->language()->getId(),
      ])
    );
  }

}
