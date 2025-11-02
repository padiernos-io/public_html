<?php

namespace Drupal\pathauto_update;

use Drupal\path_alias\PathAliasInterface;

/**
 * Defines the interface for path alias dependencies.
 */
interface PathAliasDependencyInterface {

  public const TYPE_ENTITY = 'entity';
  public const TYPE_CONFIG = 'config';

  /**
   * Get the path alias.
   *
   * @return \Drupal\path_alias\PathAliasInterface|null
   *   The path alias.
   */
  public function getPathAlias(): ?PathAliasInterface;

  /**
   * Get the dependency type.
   *
   * @return string
   *   The dependency type.
   */
  public function getDependencyType(): string;

  /**
   * Get the dependency value.
   *
   * @return string
   *   The dependency value.
   */
  public function getDependencyValue(): string;

}
