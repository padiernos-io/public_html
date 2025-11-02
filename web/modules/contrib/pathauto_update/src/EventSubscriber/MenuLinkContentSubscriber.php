<?php

namespace Drupal\pathauto_update\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyRepositoryInterface;
use Drupal\pathauto_update\PathAliasDependencyResolverInterface;
use Drupal\url_entity\UrlEntityExtractorInterface;

/**
 * Adds dependencies for menu link content entities.
 */
class MenuLinkContentSubscriber {

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
   * The URL entity extractor.
   *
   * @var \Drupal\url_entity\UrlEntityExtractorInterface
   */
  protected UrlEntityExtractorInterface $urlEntityExtractor;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    PathAliasDependencyResolverInterface $resolver,
    PathAliasDependencyRepositoryInterface $repository,
    UrlEntityExtractorInterface $urlEntityExtractor
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->resolver = $resolver;
    $this->repository = $repository;
    $this->urlEntityExtractor = $urlEntityExtractor;
  }

  /**
   * Add dependencies for menu link content entities.
   */
  public function onMenuLinkUpdate(EntityInterface $entity): void {
    if (!$entity instanceof MenuLinkContentInterface) {
      return;
    }

    $url = $entity->getUrlObject();
    $referencedEntity = $this->urlEntityExtractor->getEntityByUrl($url);
    if (!$referencedEntity instanceof EntityInterface) {
      return;
    }

    $path = $referencedEntity->toUrl()->getInternalPath();
    $pathAlias = $this->getPathAlias($path);

    if (!$pathAlias instanceof PathAliasInterface) {
      return;
    }

    // Re-resolve dependencies from this entity's pathauto pattern
    // since the menu link tree might have changed.
    $dependencies = $this->resolver->getDependencies($referencedEntity);
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
