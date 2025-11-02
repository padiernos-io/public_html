<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Url;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\Entity\PathAliasDependency;
use Drupal\pathauto_update\Plugin\QueueWorker\PathAliasUpdater;
use Drupal\url_entity\UrlEntityExtractorInterface;

/**
 * Defines the interface for path alias dependency repositories.
 */
class PathAliasDependencyRepository implements PathAliasDependencyRepositoryInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected QueueFactory $queueFactory;

  /**
   * The URL entity extractor.
   *
   * @var \Drupal\url_entity\UrlEntityExtractorInterface
   */
  protected UrlEntityExtractorInterface $urlEntityExtractor;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    Connection $database,
    QueueFactory $queueFactory,
    UrlEntityExtractorInterface $urlEntityExtractor
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->database = $database;
    $this->queueFactory = $queueFactory;
    $this->urlEntityExtractor = $urlEntityExtractor;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(PathAliasInterface $pathAlias, PathAliasDependencyCollectionInterface $dependencies): void {
    if (!$this->isSchemaInstalled()) {
      return;
    }

    foreach ($dependencies->getConfigs() as $dependentConfig) {
      $this->addDependency($pathAlias, PathAliasDependencyInterface::TYPE_CONFIG, $dependentConfig->getName());
    }

    foreach ($dependencies->getEntities() as $dependentEntity) {
      $value = implode(':', [
        $dependentEntity->getEntityTypeId(),
        $dependentEntity->id(),
        $dependentEntity->language()->getId(),
      ]);

      $this->addDependency($pathAlias, PathAliasDependencyInterface::TYPE_ENTITY, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addDependency(PathAliasInterface $pathAlias, string $type, string $value): PathAliasDependency {
    $storage = $this->entityTypeManager
      ->getStorage('path_alias_dependency');

    if ($dependency = $this->getDependency($pathAlias, $type, $value)) {
      return $dependency;
    }

    $dependency = $storage->create([
      'path_alias_id' => $pathAlias->id(),
      'dependency_type' => $type,
      'dependency_value' => $value,
    ]);

    $dependency->save();

    return $dependency;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependency(PathAliasInterface $pathAlias, string $type, string $value): ?PathAliasDependency {
    $storage = $this->entityTypeManager
      ->getStorage('path_alias_dependency');

    $query = $storage->getQuery()
      ->condition('path_alias_id', $pathAlias->id())
      ->condition('dependency_type', $type)
      ->condition('dependency_value', $value);

    $query->accessCheck(FALSE);
    $ids = $query->execute();

    if (empty($ids)) {
      return NULL;
    }

    return $storage->load(reset($ids));
  }

  /**
   * {@inheritdoc}
   */
  public function getDependenciesByType(string $dependencyType, string $dependencyValue): array {
    $storage = $this->entityTypeManager
      ->getStorage('path_alias_dependency');

    $query = $storage->getQuery()
      ->condition('dependency_type', $dependencyType)
      ->condition('dependency_value', $dependencyValue);

    $query->accessCheck(FALSE);
    $ids = $query->execute();

    if (empty($ids)) {
      return [];
    }

    return $storage->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependenciesByPathAlias(PathAliasInterface $pathAlias): array {
    $storage = $this->entityTypeManager
      ->getStorage('path_alias_dependency');

    $query = $storage->getQuery()
      ->condition('path_alias_id', $pathAlias->id());

    $query->accessCheck(FALSE);
    $ids = $query->execute();

    if (empty($ids)) {
      return [];
    }

    return $storage->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDependency(PathAliasInterface $pathAlias, string $type, string $value): void {
    if (!$this->isSchemaInstalled()) {
      return;
    }

    $dependency = $this->getDependency($pathAlias, $type, $value);
    if (!$dependency instanceof PathAliasDependencyInterface) {
      return;
    }

    $dependency->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDependenciesByType(string $dependencyType, string $dependencyValue, bool $updateDependentPathAliases = TRUE): void {
    if (!$this->isSchemaInstalled()) {
      return;
    }

    $dependencies = $this->getDependenciesByType($dependencyType, $dependencyValue);

    foreach ($dependencies as $dependency) {
      $dependency->delete();

      $pathAlias = $dependency->getPathAlias();
      if (!$pathAlias instanceof PathAliasInterface) {
        continue;
      }
      if (!$updateDependentPathAliases) {
        continue;
      }

      $url = Url::fromUri('internal:' . $pathAlias->getPath());
      $entity = $this->urlEntityExtractor->getEntityByUrl($url);
      if (!$entity instanceof EntityInterface) {
        continue;
      }

      $this->updatePathAlias($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteDependenciesByPathAlias(PathAliasInterface $pathAlias): void {
    if (!$this->isSchemaInstalled()) {
      return;
    }

    $dependencies = $this->getDependenciesByPathAlias($pathAlias);

    foreach ($dependencies as $dependency) {
      $dependency->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updatePathAliasesByType(string $dependencyType, string $dependencyValue): void {
    if (!$this->isSchemaInstalled()) {
      return;
    }

    $dependencies = $this->getDependenciesByType($dependencyType, $dependencyValue);

    foreach ($dependencies as $dependency) {
      $pathAlias = $dependency->getPathAlias();
      $url = Url::fromUri('internal:' . $pathAlias->getPath());
      $entity = $this->urlEntityExtractor->getEntityByUrl($url);

      if ($entity instanceof EntityInterface) {
        $this->updatePathAlias($entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updatePathAlias(EntityInterface $entity): void {
    $queue = $this->queueFactory->get(PathAliasUpdater::ID);
    $queue->createItem([
      'id' => $entity->id(),
      'type' => $entity->getEntityTypeId(),
      'language' => $entity->language()->getId(),
    ]);
  }

  /**
   * Check if the path_alias_dependency table exists.
   */
  protected function isSchemaInstalled(): bool {
    return $this->database->schema()
      ->tableExists('path_alias_dependency');
  }

}
