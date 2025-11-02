<?php

namespace Drupal\themespace;

/**
 * Interface for a traversable namespace objec that handles theme namespaces.
 *
 * A traversable namespace handler that include theme namespaces and allows
 * the ability to get an iterator for module and theme namespaces either
 * together or separately.
 */
interface NamespacesInterface extends \IteratorAggregate, \Traversable {

  /**
   * Gets a traversable object to iterate through namespaces.
   *
   * The themespace namespaces can include modules and themes, or if $type is
   * specified, either only module namespaces or only theme namespaces can be
   * returned to match the "type" requested.
   *
   * If $type is null, then all namespaces are returned.
   *
   * @param string|null $type
   *   Determines the value that are returned. Can be NULL, 'module' or 'theme'.
   *
   * @return \Traversable
   *   Traversable object that matches the $type specified.
   */
  public function getIterator(?string $type = NULL): \Traversable;

  /**
   * Gets a traversable object to iterate through module namespaces.
   *
   * @return \Traversable
   *   Traversable object that iterates through module namespaces.
   */
  public function getModuleIterator(): \Traversable;

  /**
   * Gets a traversable object to iterate through theme namespaces.
   *
   * @return \Traversable
   *   Traversable object that iterates through theme namespaces.
   */
  public function getThemeIterator(): \Traversable;

}
