<?php

namespace Drupal\pathauto_update\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyRepository;
use Drupal\pathauto_update\PathAliasDependencyResolverInterface;
use Drupal\url_entity\UrlEntityExtractorInterface;

/**
 * Update path aliases when dependent entities are updated.
 */
class PathAliasUpdateSubscriber {

  /**
   * The path alias dependency resolver.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyResolverInterface
   */
  protected PathAliasDependencyResolverInterface $resolver;

  /**
   * The path alias dependency repository.
   *
   * @var \Drupal\pathauto_update\PathAliasDependencyRepository
   */
  protected PathAliasDependencyRepository $repository;

  /**
   * The URL entity extractor.
   *
   * @var \Drupal\url_entity\UrlEntityExtractorInterface
   */
  protected UrlEntityExtractorInterface $urlEntityExtractor;

  public function __construct(
    PathAliasDependencyResolverInterface $resolver,
    PathAliasDependencyRepository $repository,
    UrlEntityExtractorInterface $urlEntityExtractor
  ) {
    $this->resolver = $resolver;
    $this->repository = $repository;
    $this->urlEntityExtractor = $urlEntityExtractor;
  }

  /**
   * Resolve and store any dependencies of the updated path alias.
   */
  public function onPathAliasUpdate(PathAliasInterface $pathAlias): void {
    $url = Url::fromUri('internal:' . $pathAlias->getPath());
    $entity = $this->urlEntityExtractor->getEntityByUrl($url);

    if (!$entity instanceof EntityInterface) {
      return;
    }

    $dependencies = $this->resolver->getDependencies($entity);
    $this->repository->addDependencies($pathAlias, $dependencies);
  }

}
