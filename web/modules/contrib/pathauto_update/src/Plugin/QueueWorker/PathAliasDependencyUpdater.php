<?php

namespace Drupal\pathauto_update\Plugin\QueueWorker;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyRepositoryInterface;
use Drupal\pathauto_update\PathAliasDependencyResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Resolves and stores dependencies of a given entity.
 *
 * @QueueWorker(
 *   id = \Drupal\pathauto_update\Plugin\QueueWorker\PathAliasDependencyUpdater::ID,
 *   title = @Translation("Resolves and stores dependencies of a given entity."),
 *   cron = {"time" : 30}
 * )
 */
class PathAliasDependencyUpdater extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public const ID = 'pathauto_update_path_alias_dependency_updater';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The path alias dependency resolver.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyResolverInterface
   */
  protected PathAliasDependencyResolverInterface $resolver;

  /**
   * The path alias dependency repository.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyRepositoryInterface
   */
  protected PathAliasDependencyRepositoryInterface $repository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->resolver = $container->get('pathauto_update.path_alias_dependency.resolver');
    $instance->repository = $container->get('pathauto_update.path_alias_dependency.repository');

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
    $pathAlias = $this->getPathAlias($entity->toUrl()->getInternalPath());

    if ($pathAlias === NULL) {
      return;
    }

    $dependencies = $this->resolver->getDependencies($entity);
    $this->repository->addDependencies($pathAlias, $dependencies);
  }

  /**
   * Get the path alias entity of a given path.
   */
  protected function getPathAlias(string $path): ?PathAliasInterface {
    $entities = $this->entityTypeManager
      ->getStorage('path_alias')
      ->loadByProperties([
        'path' => '/' . ltrim($path, '/'),
      ]);

    return array_pop($entities);
  }

}
