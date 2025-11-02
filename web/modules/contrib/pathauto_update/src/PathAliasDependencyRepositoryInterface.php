<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Entity\EntityInterface;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\Entity\PathAliasDependency;

/**
 * Defines the interface for path alias dependency repositories.
 */
interface PathAliasDependencyRepositoryInterface {

  /**
   * Add dependencies for a path alias.
   */
  public function addDependencies(PathAliasInterface $pathAlias, PathAliasDependencyCollectionInterface $dependencies): void;

  /**
   * Add a dependency for a path alias.
   */
  public function addDependency(PathAliasInterface $pathAlias, string $type, string $value): PathAliasDependency;

  /**
   * Get a dependency for a path alias.
   */
  public function getDependency(PathAliasInterface $pathAlias, string $type, string $value): ?PathAliasDependency;

  /**
   * Get dependencies by type.
   *
   * @return \Drupal\pathauto_update\Entity\PathAliasDependency[]
   *   An array of path alias dependencies.
   */
  public function getDependenciesByType(string $dependencyType, string $dependencyValue): array;

  /**
   * Get dependencies by path alias.
   *
   * @return \Drupal\pathauto_update\Entity\PathAliasDependency[]
   *   An array of path alias dependencies.
   */
  public function getDependenciesByPathAlias(PathAliasInterface $pathAlias): array;

  /**
   * Delete a dependency for a path alias.
   *
   * @param \Drupal\path_alias\PathAliasInterface $pathAlias
   *   The path alias.
   * @param string $type
   *   The dependency type.
   * @param string $value
   *   The dependency value.
   */
  public function deleteDependency(PathAliasInterface $pathAlias, string $type, string $value): void;

  /**
   * Delete dependencies by type.
   *
   * @param string $dependencyType
   *   The dependency type.
   * @param string $dependencyValue
   *   The dependency value.
   * @param bool $updateDependentPathAliases
   *   Whether to update dependent path aliases.
   */
  public function deleteDependenciesByType(string $dependencyType, string $dependencyValue, bool $updateDependentPathAliases = TRUE): void;

  /**
   * Delete dependencies by path alias.
   *
   * @param \Drupal\path_alias\PathAliasInterface $pathAlias
   *   The path alias.
   */
  public function deleteDependenciesByPathAlias(PathAliasInterface $pathAlias): void;

  /**
   * Update path aliases by type.
   *
   * @param string $dependencyType
   *   The dependency type.
   * @param string $dependencyValue
   *   The dependency value.
   */
  public function updatePathAliasesByType(string $dependencyType, string $dependencyValue): void;

  /**
   * Update a path alias.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function updatePathAlias(EntityInterface $entity): void;

}
