<?php

namespace Drupal\pathauto_update\Plugin\QueueWorker;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\pathauto\PathautoGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates or updates an alias for a given entity.
 *
 * @QueueWorker(
 *   id = \Drupal\pathauto_update\Plugin\QueueWorker\PathAliasUpdater::ID,
 *   title = @Translation("Creates or updates an alias for a given entity."),
 *   cron = {"time" : 30}
 * )
 */
class PathAliasUpdater extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public const ID = 'pathauto_update_path_alias_updater';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The alias generator.
   *
   * @var \Drupal\pathauto\PathautoGeneratorInterface
   */
  protected PathautoGeneratorInterface $aliasGenerator;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected CacheTagsInvalidatorInterface $tagsInvalidator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->tagsInvalidator = $container->get('cache_tags.invalidator');
    $instance->aliasGenerator = $container->get('pathauto.generator');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $entity = $this->entityTypeManager
      ->getStorage($data['type'])
      ->load($data['id']);

    if (!$entity instanceof ContentEntityInterface) {
      return;
    }

    if (!$entity->hasTranslation($data['language'])) {
      return;
    }

    $entity = $entity->getTranslation($data['language']);
    $this->aliasGenerator->updateEntityAlias($entity, 'update');
    $this->tagsInvalidator->invalidateTags($entity->getCacheTagsToInvalidate());
  }

}
